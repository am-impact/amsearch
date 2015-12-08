<?php
namespace Craft;

/**
 * Represents a paginate node.
 */
class AmSearchPaginate_Node extends \Twig_Node
{
    /**
     * Compiles the node to PHP.
     *
     * @param \Twig_Compiler $compiler
     *
     * @return null
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            // the (array) cast bypasses a PHP 5.2.6 bug
            //->write("\$context['_parent'] = (array) \$context;\n")
            ->write('list(')
            ->subcompile($this->getNode('paginateTarget'))
            ->raw(', ')
            ->subcompile($this->getNode('elementsTarget'))
            ->raw(') = \Craft\AmSearchTemplateHelper::amSearchPaginate(')
            ->subcompile($this->getNode('criteria'))
            ->raw(");\n");
    }
}
