<?php

namespace Fuz\QuickStartBundle\Services;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * quickstart.captcha
 */
class Captcha
{
    const CHECK_URL = 'https://www.google.com/recaptcha/api/siteverify';
    const POST_PARAM = 'g-recaptcha-response';

    protected $logger;
    protected $session;
    protected $sessionIpRepo;
    protected $sessionHitRepo;
    protected $ipLimitRepo;
    protected $config;

    public function __construct(LoggerInterface $logger, Session $session, EntityManager $em, $noCaptcha)
    {
        $this->logger = $logger;
        $this->session = $session;
        $this->sessionIpRepo = $em->getRepository('FuzQuickStartBundle:CaptchaSessionIp');
        $this->sessionHitRepo = $em->getRepository('FuzQuickStartBundle:CaptchaSessionHit');
        $this->ipLimitRepo = $em->getRepository('FuzQuickStartBundle:CaptchaIpLimit');
        $this->config = $noCaptcha;
    }

    public function check(Request $request, $strategy)
    {
        if (!array_key_exists($strategy, $this->config['strategies'])) {
            throw new \LogicException("Unknown strategy given: {$strategy}.");
        }

        $this->clearExpiredInformation($strategy);

        $ip = ip2long($request->getClientIp()) ?: ip2long('127.0.0.1');
        $sessionId = $this->session->getId();

        if ($this->validateCaptcha($request)) {
            $this->ipLimitRepo->increaseLimit($ip, $this->config['sessions_per_ip']['max']);

            if ($this->config['strategies'][$strategy]['reset']) {
                $this->sessionHitRepo->resetHits($sessionId, $strategy);
            }

            return true;
        }

        if ($this->hasReachedLimits($ip, $sessionId, $strategy)) {
            return false;
        }

        $this->sessionIpRepo->record($ip, $sessionId);
        $this->ipLimitRepo->record($ip, $this->config['sessions_per_ip']['max']);
        $this->sessionHitRepo->record($sessionId, $strategy);

        return true;
    }

    public function validateCaptcha(Request $request)
    {
        $response = $request->request->get(self::POST_PARAM);
        if ($response) {
            $ip = ip2long($request->getClientIp()) ? $request->getClientIp() : '127.0.0.1';

            $parameters = array(
                'secret' => $this->config['secret_key'],
                'response' => $response,
                'remoteip' => $ip,
            );

            $query = self::CHECK_URL.'?'.http_build_query($parameters);
            $json = json_decode(file_get_contents($query));

            return $json->success;
        }

        return false;
    }

    protected function clearExpiredInformation($strategy)
    {
        $dateIntervalIp = \DateInterval::createFromDateString("{$this->config['sessions_per_ip']['delay']} seconds");
        $expiryDateIp = new \DateTime();
        $expiryDateIp->sub($dateIntervalIp);

        $this->sessionIpRepo->deleteExpired($expiryDateIp);
        $this->ipLimitRepo->deleteExpired($expiryDateIp);

        $dateIntervalStrategy = \DateInterval::createFromDateString("{$this->config['strategies'][$strategy]['delay']} seconds");
        $expiryDateStrategy = new \DateTime();
        $expiryDateStrategy->sub($dateIntervalStrategy);

        $this->sessionHitRepo->deleteExpired($strategy, $expiryDateStrategy);
    }

    protected function hasReachedLimits($ip, $sessionId, $strategy)
    {
        $sessionsPerIp = $this->ipLimitRepo->findOneByIp($ip);
        $sessionsCount = $this->sessionIpRepo->count($ip);
        if ($sessionsPerIp && $sessionsCount >= $sessionsPerIp->getLimit()) {
            return true;
        }

        if ($this->sessionHitRepo->getHits($sessionId, $strategy) >= $this->config['strategies'][$strategy]['hits']) {
            return true;
        }

        return false;
    }
}
