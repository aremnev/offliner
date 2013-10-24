<?php

namespace Thumbtack\OfflinerBundle\Security;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as BaseOAuthUserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Thumbtack\OfflinerBundle\Entity\User;

class TempResponse implements UserResponseInterface{


    /**
     * Get the api response.
     *
     * @return array
     */
    public function getResponse() {
        // TODO: Implement getResponse() method.
    }

    /**
     * Set the raw api response.
     *
     * @param string|array $response
     */
    public function setResponse($response) {
        // TODO: Implement setResponse() method.
    }

    /**
     * Get the resource owner responsible for the response.
     *
     * @return ResourceOwnerInterface
     */
    public function getResourceOwner() {
        // TODO: Implement getResourceOwner() method.
    }

    /**
     * Set the resource owner for the response.
     *
     * @param ResourceOwnerInterface $resourceOwner
     */
    public function setResourceOwner(ResourceOwnerInterface $resourceOwner) {
        // TODO: Implement setResourceOwner() method.
    }

    /**
     * Get the unique user identifier.
     *
     * Note that this is not always common known "username" because of implementation
     * in Symfony2 framework. For more details follow link below.
     * @link https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/Security/Core/User/UserProviderInterface.php#L20-L28
     *
     * @return string
     */
    public function getUsername() {
        // TODO: Implement getUsername() method.
    }

    /**
     * Get the username to display.
     *
     * @return string
     */
    public function getNickname() {
        return "Temp User";
    }

    /**
     * Get the real name of user.
     *
     * @return null|string
     */
    public function getRealName() {
        // TODO: Implement getRealName() method.
    }

    /**
     * Get the email address.
     *
     * @return null|string
     */
    public function getEmail() {
       return "temp@temp.com";
    }

    /**
     * Get the url to the profile picture.
     *
     * @return null|string
     */
    public function getProfilePicture() {
        // TODO: Implement getProfilePicture() method.
    }

    /**
     * Get the access token used for the request.
     *
     * @return string
     */
    public function getAccessToken() {
        // TODO: Implement getAccessToken() method.
    }

    /**
     * Get the access token used for the request.
     *
     * @return null|string
     */
    public function getRefreshToken() {
        // TODO: Implement getRefreshToken() method.
    }

    /**
     * Get oauth token secret used for the request.
     *
     * @return null|string
     */
    public function getTokenSecret() {
        // TODO: Implement getTokenSecret() method.
    }

    /**
     * Get the info when token will expire.
     *
     * @return null|string
     */
    public function getExpiresIn() {
        // TODO: Implement getExpiresIn() method.
    }

    /**
     * Set the raw token data from the request.
     *
     * @param OAuthToken $token
     */
    public function setOAuthToken(OAuthToken $token) {
        // TODO: Implement setOAuthToken() method.
    }
}
