<?php

namespace Arachne\Forms\Latte;

use Latte\Compiler;
use Latte\Macros\MacroSet;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FormMacros extends MacroSet
{
    /**
     * @param Compiler $compiler
     */
    public static function install(Compiler $compiler)
    {
        $me = new static($compiler);
        $me->addMacro('formComplete', '$_formRenderer = $this->global->formRenderer; echo $_formRenderer()->renderBlock(%node.word, \'form\', %node.array?)');
        $me->addMacro('formStart', '$_formRenderer = $this->global->formRenderer; echo $_formRenderer()->renderBlock(%node.word, \'form_start\', %node.array?)');
        $me->addMacro('formEnd', '$_formRenderer = $this->global->formRenderer; echo $_formRenderer()->renderBlock(%node.word, \'form_end\', %node.array?)');
        $me->addMacro('formLabel', '$_formRenderer = $this->global->formRenderer; echo $_formRenderer()->searchAndRenderBlock(%node.word, \'label\', %node.array?)');
        $me->addMacro('formErrors', '$_formRenderer = $this->global->formRenderer; echo $_formRenderer()->searchAndRenderBlock(%node.word, \'errors\', %node.array?)');
        $me->addMacro('formWidget', '$_formRenderer = $this->global->formRenderer; echo $_formRenderer()->searchAndRenderBlock(%node.word, \'widget\', %node.array?)');
        $me->addMacro('formRow', '$_formRenderer = $this->global->formRenderer; echo $_formRenderer()->searchAndRenderBlock(%node.word, \'row\', %node.array?)');
        $me->addMacro('formRest', '$_formRenderer = $this->global->formRenderer; echo $_formRenderer()->searchAndRenderBlock(%node.word, \'rest\', %node.array?)');
    }
}
