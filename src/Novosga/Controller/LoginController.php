<?php

namespace Novosga\Controller;

use Novosga\App;
use Novosga\Context;

/**
 * LoginController.
 *
 * 
 */
class LoginController extends InternalController
{
    public function index(Context $context)
    {
        if ($this->app()->getAcessoService()->isLogged($context)) {
            if ($this->app()->getAcessoService()->isValidSession($context)) {
                if ($context->getModulo()) {
                    $this->app()->gotoModule();
                } else {
                    $this->app()->gotoHome();
                }
            } else {
                $user = $context->getUser();
                if ($user != null) {
                    if (!$user->isAtivo()) {
                        $this->app()->view()->set('error', _('Sesión expirada. Por favor ingrese nuevamente.'));
                    } else {
                        $this->app()->view()->set('error', _('Sesión inválida. Posiblemente su usuario esté siendo utilizado en otro equipo'));
                    }
                    $context->setUser(null);
                }
            }
        }
    }

    public function validate(Context $context)
    {
        $username = $context->request()->post('username');
        $password = $context->request()->post('password');
        $error = null;
        if (!empty($username) && !empty($password)) {
            $em = $context->database()->createEntityManager();
            $config = \Novosga\Model\Configuracao::get($em, \Novosga\Auth\AuthenticationProvider::KEY);
            $auth = ($config) ? $config->getValor() : array();
            $provider = App::authenticationFactory()->create($context, $auth);
            $user = $provider->auth($username, $password);
            if ($user) {
                // atualizando o session id
                $user->setSessionId(session_id());
                $user->setUltimoAcesso(new \DateTime());
                $em->merge($user);
                $em->flush();
                // caso o usuario so tenha acesso a uma unica unidade e nao tem uma guardada pelo ultimo acesso, ja define a primeira como atual
                $us = new \Novosga\Model\Util\UsuarioSessao($user);
                $us->setEm($em);
                if (!$us->getUnidade()) {
                    $unidades = $this->app()->getAcessoService()->unidades($context, $us);
                    if (sizeof($unidades) == 1) {
                        $us->setUnidade($unidades[0]);
                    }
                }
                $context->setUser($us);
            } else {
                $error = _('Usuario o contraseña inválido');
            }
        }
        if (!$error) {
            $this->app()->gotoHome();
        } else {
            $this->app()->flash('error', $error);
            $this->app()->gotoLogin();
        }
    }
}
