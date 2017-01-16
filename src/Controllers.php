<?php

namespace Bolt\Site\Installer;

use Silex;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Controllers implements ControllerProviderInterface
{
    /** @var Silex\Application */
    private $app;

    public function connect(Silex\Application $app)
    {
        $this->app = $app;

        /** @var $ctr \Silex\ControllerCollection */
        $ctr = $app['controllers_factory'];

        $ctr->get('/', [$this, 'index'])
            ->bind('index');

        $app->error([$this, 'error']);

        return $ctr;
    }

    /**
     * @return Response
     */
    public function index()
    {
        $context = [];
        $html = $this->render('index.twig', $context);
        $response = new Response($html);

        return $response;
    }

    protected function render($template, array $variables = [])
    {
        return $this->app['twig']->render($template, $variables);
    }

    /**
     * Controller for error pages.
     *
     * @param \Exception $e
     * @param Request    $request
     * @param integer    $code
     *
     * @return Response|null
     */
    public function error(\Exception $e, Request $request, $code)
    {
        $requestUri = explode('/', $request->getRequestUri());

        // Don't trap Symfony shizzle.
        if (in_array($requestUri[1], ['a', '_profiler']) || $this->app['debug']) {
            return null;
        }

        // If we have a 404 error, show the 404 page.
        if ($code == 404) {
            return $this->render('');
        }

        // Otherwise, we return, and let Silex handle it.
        return null;
    }

}
