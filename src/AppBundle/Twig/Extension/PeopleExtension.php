<?php

namespace AppBundle\Twig\Extension;

class PeopleExtension extends \Twig_Extension
{
    protected $photoUrl;

    public function __construct($photoUrl)
    {
        $this->photoUrl = $photoUrl;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('peoplePicture', [$this, 'peoplePicture']),
            new \Twig_SimpleFilter('peopleName', [$this, 'peopleName']),
        ];
    }

    public function peoplePicture($email)
    {
        return $this->photoUrl . md5(strtolower(trim($email)));
    }

    public function peopleName($email)
    {
        if (stripos($email, 'dashboard') !== false) {
            return 'On Board';
        }
        $tokens = explode('.', $email);
        return ucfirst($tokens[0]);
    }

    public function getName()
    {
        return 'people';
    }
}
