<?php

namespace Novosga\Twig;

/**
 * Resources Twig filter.
 *
 * 
 */
class Extensions extends \Twig_Extension
{
    public function getName()
    {
        return 'novosga';
    }

    public function getFunctions()
    {
        return array(
            new ResourcesFunction(),
        );
    }

    public function getFilters()
    {
        return array(
            new SecFormat(),
        );
    }
}
