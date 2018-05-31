<?php
/**
 * Form Action Class
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

namespace App\Action;

use Slim\Views\Twig;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * A class to be invoked for the form action.
 */
class FormAction
{
    // Handle sessions including CSRF tokens.
    use \Vperyod\SessionHandler\SessionRequestAwareTrait;

    /**
     * View renderer.
     * @var Twig
     */
    private $view;

    /**
     * Construct the action with objects and configuration.
     * @param Twig $view View renderer.
     */
    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    /**
     * Method called when class is invoked as an action.
     * @param Request $req The request for the action.
     * @param Response $res The response from the action.
     * @param array $args The arguments for the action.
     * @return Response The response from the action.
     */
    public function __invoke(Request $req, Response $res, array $args)
    {
        // Render form template with CSRF token.
        $args['csrf'] = $this->getCsrfSpec($req);
        return $this->view->render($res, 'form.html.twig', $args);
    }
}
