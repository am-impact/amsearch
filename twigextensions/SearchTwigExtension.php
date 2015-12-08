<?php
namespace Craft;

use Twig_Extension;

class SearchTwigExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'Search';
    }

    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
     */
    public function getTokenParsers()
    {
        return array(
            new AmSearchPaginate_TokenParser(),
        );
    }
}
