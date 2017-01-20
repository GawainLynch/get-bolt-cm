<?php

namespace Bolt\Site\Installer;

use Bolt\Site\Installer\Entity;
use Bolt\Site\Installer\Entity\Download;
use Bolt\Site\Installer\Exception\InvalidVersionException;
use Doctrine\ORM\EntityManager;
use Silex;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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
            ->bind('index')
        ;

        $ctr->get('/latest', [$this, 'latest'])
            ->bind('latest')
        ;

        $ctr->get('/installer', [$this, 'installer'])
            ->bind('installer')
        ;

        $ctr->get('/download/{majorMinor}/{majorMinorPatch}', [$this, 'download'])
            ->bind('download')
            ->value('majorMinorPatch', null)
            ->assert('majorMinor', Validator::REGEX_MAJOR_MINOR)
            ->assert('majorMinorPatch', Validator::REGEX_MAJOR_MINOR_PATCH)
        ;

        $app->error([$this, 'error']);

        return $ctr;
    }

    /**
     * @return Response
     */
    public function index()
    {
        $context = [
            'latest' => $this->getVersionManager()->getLatest()
        ];
        $html = $this->render('index.twig', $context);
        $response = new Response($html);

        return $response;
    }

    /**
     * @return string
     */
    public function latest()
    {
        return (string) $this->getVersionManager()->getLatest();
    }

    /**
     * @param string      $majorMinor
     * @param string|null $majorMinorPatch
     *
     * @return Response
     */
    public function download($majorMinor, $majorMinorPatch)
    {
        $resolver = DownloadResolver::create()
            ->setMajorMinor($majorMinor)
            ->setMajorMinorPatch($majorMinorPatch)
        ;

        try {
            $url = $resolver->getUrl();
        } catch (InvalidVersionException $e) {
            return new Response(sprintf('%s', $e->getMessage()), Response::HTTP_FORBIDDEN);
        }

        if ($this->app['debug']) {
            $logger = $this->app['logger'];
            $logger->info("Minor is: $majorMinor | Patch is: $majorMinorPatch | Download URL: $url");
        }

        $this->logDownload($majorMinorPatch);

        return new RedirectResponse($resolver->getUrl());
    }

    /**
     * @return BinaryFileResponse
     */
    public function installer()
    {
        $response = new BinaryFileResponse('../web/bolt.phar', Response::HTTP_OK);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'bolt'
        );

        return $response;
    }

    /**
     * Render a template.
     *
     * @param string $template
     * @param array  $variables
     *
     * @return string
     */
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

    /**
     * Record a request in the database.
     *
     * @param $majorMinorPatch
     */
    protected function logDownload($majorMinorPatch)
    {
        /** @var EntityManager $em */
        $em = $this->app['orm.em'];
        $em->getRepository(Entity\Download::class);
        $d = (new Download())
            ->setVersion($majorMinorPatch)
            ->setPhpVersion($this->getRequest()->query->get('php'))
            ->setDate(new \DateTime())
            ->setIpAddress($this->getRequest()->getClientIp())
        ;
        $em->persist($d);
        $em->flush($d);
    }

    /**
     * @return Request|null
     */
    private function getRequest()
    {
        /** @var RequestStack $stack */
        $stack = $this->app['request_stack'];

        return $stack->getCurrentRequest();
    }

    /**
     * @return VersionManager
     */
    private function getVersionManager()
    {
        return new VersionManager();
    }
}
