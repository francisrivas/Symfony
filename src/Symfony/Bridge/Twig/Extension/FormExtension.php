<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Twig\Extension;

use Symfony\Bridge\Twig\TokenParser\FormThemeTokenParser;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Symfony\Component\Form\Util\FormUtil;

/**
 * FormExtension extends Twig with form capabilities.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FormExtension extends \Twig_Extension
{
    protected $csrfProvider;
    protected $resources;
    protected $blocks;
    protected $environment;
    protected $themes;
    protected $varStack;
    protected $template;

    public function __construct(CsrfProviderInterface $csrfProvider = null, array $resources = array())
    {
        $this->csrfProvider = $csrfProvider;
        $this->themes = new \SplObjectStorage();
        $this->varStack = array();
        $this->blocks = new \SplObjectStorage();
        $this->resources = $resources;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Sets a theme for a given view.
     *
     * @param FormView     $view      A FormView instance
     * @param array|string $resources An array of resource names|a resource name
     */
    public function setTheme(FormView $view, $resources)
    {
        $this->themes->attach($view, (array) $resources);
        $this->blocks = new \SplObjectStorage();
    }

    /**
     * Returns the token parser instance to add to the existing list.
     *
     * @return array An array of Twig_TokenParser instances
     */
    public function getTokenParsers()
    {
        return array(
            // {% form_theme form "SomeBundle::widgets.twig" %}
            new FormThemeTokenParser(),
        );
    }

    public function getFunctions()
    {
        return array(
            'form_enctype'             => new \Twig_Function_Method($this, 'renderEnctype', array('is_safe' => array('html'))),
            'form_widget'              => new \Twig_Function_Method($this, 'renderWidget', array('is_safe' => array('html'))),
            'form_errors'              => new \Twig_Function_Method($this, 'renderErrors', array('is_safe' => array('html'))),
            'form_label'               => new \Twig_Function_Method($this, 'renderLabel', array('is_safe' => array('html'))),
            'form_row'                 => new \Twig_Function_Method($this, 'renderRow', array('is_safe' => array('html'))),
            'form_rest'                => new \Twig_Function_Method($this, 'renderRest', array('is_safe' => array('html'))),
            'csrf_token'               => new \Twig_Function_Method($this, 'getCsrfToken'),
            '_form_is_choice_group'    => new \Twig_Function_Method($this, 'isChoiceGroup', array('is_safe' => array('html'))),
            '_form_is_choice_selected' => new \Twig_Function_Method($this, 'isChoiceSelected', array('is_safe' => array('html'))),
        );
    }

    public function getFilters()
    {
        return array(
            'humanize' => new \Twig_Filter_Function(__NAMESPACE__.'\humanize'),
        );
    }

    public function isChoiceGroup($label)
    {
        return FormUtil::isChoiceGroup($label);
    }

    public function isChoiceSelected(FormView $view, ChoiceView $choice)
    {
        return FormUtil::isChoiceSelected($choice->getValue(), $view->getVar('value'));
    }

    /**
     * Renders the HTML enctype in the form tag, if necessary
     *
     * Example usage in Twig templates:
     *
     *     <form action="..." method="post" {{ form_enctype(form) }}>
     *
     * @param FormView $view The view for which to render the encoding type
     *
     * @return string The html markup
     */
    public function renderEnctype(FormView $view)
    {
        return $this->render($view, 'enctype');
    }

    /**
     * Renders a row for the view.
     *
     * @param FormView $view      The view to render as a row
     * @param array    $variables An array of variables
     *
     * @return string The html markup
     */
    public function renderRow(FormView $view, array $variables = array())
    {
        return $this->render($view, 'row', $variables);
    }

    /**
     * Renders views which have not already been rendered.
     *
     * @param FormView $view      The parent view
     * @param array    $variables An array of variables
     *
     * @return string The html markup
     */
    public function renderRest(FormView $view, array $variables = array())
    {
        return $this->render($view, 'rest', $variables);
    }

    /**
     * Renders the HTML for a given view
     *
     * Example usage in Twig:
     *
     *     {{ form_widget(view) }}
     *
     * You can pass options during the call:
     *
     *     {{ form_widget(view, {'attr': {'class': 'foo'}}) }}
     *
     *     {{ form_widget(view, {'separator': '+++++'}) }}
     *
     * @param FormView $view      The view to render
     * @param array    $variables Additional variables passed to the template
     *
     * @return string The html markup
     */
    public function renderWidget(FormView $view, array $variables = array())
    {
        return $this->render($view, 'widget', $variables);
    }

    /**
     * Renders the errors of the given view
     *
     * @param FormView $view The view to render the errors for
     *
     * @return string The html markup
     */
    public function renderErrors(FormView $view)
    {
        return $this->render($view, 'errors');
    }

    /**
     * Renders the label of the given view
     *
     * @param FormView $view      The view to render the label for
     * @param string   $label     Label name
     * @param array    $variables Additional variables passed to the template
     *
     * @return string The html markup
     */
    public function renderLabel(FormView $view, $label = null, array $variables = array())
    {
        if ($label !== null) {
            $variables += array('label' => $label);
        }

        return $this->render($view, 'label', $variables);
    }

    /**
     * Renders a template.
     *
     * 1. This function first looks for a block named "_<view id>_<section>",
     * 2. if such a block is not found the function will look for a block named
     *    "<type name>_<section>",
     * 3. the type name is recursively replaced by the parent type name until a
     *    corresponding block is found
     *
     * @param FormView $view      The form view
     * @param string   $section   The section to render (i.e. 'row', 'widget', 'label', ...)
     * @param array    $variables Additional variables
     *
     * @return string The html markup
     *
     * @throws FormException if no template block exists to render the given section of the view
     */
    protected function render(FormView $view, $section, array $variables = array())
    {
        $mainTemplate = in_array($section, array('widget', 'row'));

        if ($mainTemplate && $view->isRendered()) {
            return '';
        }

        if (null === $this->template) {
            $this->template = reset($this->resources);
            if (!$this->template instanceof \Twig_Template) {
                $this->template = $this->environment->loadTemplate($this->template);
            }
        }

        $rendering = $view->getVar('id').$section;
        $blocks = $this->getBlocks($view);

        if (isset($this->varStack[$rendering])) {
            $typeIndex = $this->varStack[$rendering]['typeIndex'] - 1;
            $types = $this->varStack[$rendering]['types'];
            $this->varStack[$rendering]['variables'] = array_replace_recursive($this->varStack[$rendering]['variables'], $variables);
        } else {
            $types = $view->getVar('types');
            $types[] = '_'.$view->getVar('theme_id');
            $typeIndex = count($types) - 1;
            $this->varStack[$rendering] = array(
                'variables' => array_replace_recursive($view->getVars(), $variables),
                'types'     => $types,
            );
        }

        do {
            $types[$typeIndex] .= '_'.$section;

            if (isset($blocks[$types[$typeIndex]])) {
                $this->varStack[$rendering]['typeIndex'] = $typeIndex;

                $context = $this->environment->mergeGlobals($this->varStack[$rendering]['variables']);

                // we do not call renderBlock here to avoid too many nested level calls (XDebug limits the level to 100 by default)
                ob_start();
                $this->template->displayBlock($types[$typeIndex], $context, $blocks);
                $html = ob_get_clean();

                if ($mainTemplate) {
                    $view->setRendered();
                }

                unset($this->varStack[$rendering]);

                return $html;
            }
        } while (--$typeIndex >= 0);

        throw new FormException(sprintf(
            'Unable to render the form as none of the following blocks exist: "%s".',
            implode('", "', array_reverse($types))
        ));
    }

    /**
     * Returns a CSRF token.
     *
     * Use this helper for CSRF protection without the overhead of creating a
     * form.
     *
     * <code>
     * <input type="hidden" name="token" value="{{ csrf_token('rm_user_' ~ user.id) }}">
     * </code>
     *
     * Check the token in your action using the same intention.
     *
     * <code>
     * $csrfProvider = $this->get('form.csrf_provider');
     * if (!$csrfProvider->isCsrfTokenValid('rm_user_'.$user->getId(), $token)) {
     *     throw new \RuntimeException('CSRF attack detected.');
     * }
     * </code>
     *
     * @param string $intention The intention of the protected action
     *
     * @return string A CSRF token
     */
    public function getCsrfToken($intention)
    {
        if (!$this->csrfProvider instanceof CsrfProviderInterface) {
            throw new \BadMethodCallException('CSRF token can only be generated if a CsrfProviderInterface is injected in the constructor.');
        }

        return $this->csrfProvider->generateCsrfToken($intention);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'form';
    }

    /**
     * Returns the blocks used to render the view.
     *
     * Templates are looked for in the resources in the following order:
     *   * resources from the themes (and its parents)
     *   * resources from the themes of parent views (up to the root view)
     *   * default resources
     *
     * @param FormView $view The view
     *
     * @return array An array of Twig_TemplateInterface instances
     */
    protected function getBlocks(FormView $view)
    {
        if (!$this->blocks->contains($view)) {
            $rootView = !$view->hasParent();

            $templates = $rootView ? $this->resources : array();

            if (isset($this->themes[$view])) {
                $templates = array_merge($templates, $this->themes[$view]);
            }

            $blocks = array();

            foreach ($templates as $template) {
                if (!$template instanceof \Twig_Template) {
                    $template = $this->environment->loadTemplate($template);
                }

                $templateBlocks = array();
                do {
                    $templateBlocks = array_merge($template->getBlocks(), $templateBlocks);
                } while (false !== $template = $template->getParent(array()));
                $blocks = array_merge($blocks, $templateBlocks);
            }

            if (!$rootView) {
                $blocks = array_merge($this->getBlocks($view->getParent()), $blocks);
            }

            $this->blocks->attach($view, $blocks);
        } else {
            $blocks = $this->blocks[$view];
        }

        return $blocks;
    }
}

function humanize($text)
{
    return ucfirst(trim(strtolower(preg_replace('/[_\s]+/', ' ', $text))));
}
