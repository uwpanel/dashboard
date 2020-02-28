<?php

/**
 * Controller for handling static pages, although
 * It can be used like any other controller using
 * of Templates, Layouts and Partials.
 * the parameters passed to the show () method indicate views that are in views / pages /
 * maintaining its directory structure
 * Example:
 * 
 * Ex.
 * dominio.com/pages/show/organizacion/privacidad
 * will show the view views/pages/organizacion/privacidad.phtml
 *
 * dominio.com/pages/show/aviso
 * will show the view views/pages/aviso.phtml
 *
 * You can also use the routes.ini to call it with another name,
 * /aviso = pages/show/aviso
 * So when going to dominio.com/aviso  will show the view views/pages/aviso.phtml
 *
 * /organizacion/* = pages/show/organizacion/*
 * When going to dominio.com/organizacion/privacidad will show the view in views/organizacion/privacidad.phtml
 *
 * You can also use Helpers
 * <?= link_to('pages/show/aviso', 'Go Notice') ?>
 * It will show a link that by clicking will go to dominio.com/pages/show/aviso
 *
 */
class PagesController extends AppController
{
    protected function before_filter()
    {
        $this->limit_params = false;
        // If it is AJAX ,send only the view
        if (Input::isAjax()) {
            View::template(null);
        }
        // Use controller / page directly
        if (!method_exists($this, $this->action_name)) {
            array_unshift($this->parameters, $this->action_name);
            $this->action_name = 'show';
        }
    }

    public function show()
    {
        View::select(implode('/', $this->parameters));
    }
}
