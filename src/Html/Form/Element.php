<?php
/**
 * Created by PhpStorm.
 * User: mncedim
 * Date: 15/09/24
 * Time: 7:13 AM
 */

namespace Mncedim\Html\Form;

/**
 * Class Element
 * @package Mncedim\Html\Form
 */
class Element
{
    /**
     * Name of the element
     * @var string
     */
    private $name;

    /**
     * type of the element
     * @var string
     */
    private $type;

    /**
     * all element settings and attributes
     * @var array
     */
    private $properties;

    /**
     * @var array
     */
    private $errors = array();

    /**
     * @var bool
     */
    private $hideLabel = false;

    /**
     * @var bool
     */
    private $hideErrors = false;

    /**
     * @var string
     */
    protected $errorCssClass = 'error';

    /**
     * the form this element belongs to
     * @var null|Form
     */
    public $form;

    /**
     * All form element templates
     * @var array
     */
    private static $templates = array(
        'label'
            => '<label for="{{id}}">{{text}}</label>',
        'text'
            => '<input type="text" name="{{name}}" id="{{id}}" value="{{value}}" class="{{class}}" {{extra}} />',
        'password'
            => '<input type="password" name="{{name}}" id="{{id}}" value="{{value}}" class="{{class}}" {{extra}} />',
        'email'
            => '<input type="email" name="{{name}}" id="{{id}}" value="{{value}}" class="{{class}}" {{extra}} />',
        'textarea'
            => '<textarea name="{{name}}" id="{{id}}" class="{{class}}" {{extra}}>{{value}}</textarea>',
        'select'
            => '<select name="{{name}}" id="{{id}}" class="{{class}}" {{extra}}>{{value}}</select>',
        'checkbox'
            => '<input type="checkbox" name="{{name}}" id="{{id}}" class="{{class}}" {{value}} {{extra}} />',
        'radio'
            => '<input type="radio" name="{{name}}" id="{{id}}" class="{{class}}" {{value}} {{extra}} />',
        'hidden'
            => '<input type="hidden" name="{{name}}" id="{{id}}" class="{{class}}" {{extra}} value="{{value}}" />',
        'file'
            => '<input type="file" name="{{name}}" id="{{id}}" class="{{class}}" {{extra}}/>{{value}}',
        'url'
            => '<input type="url" name="{{name}}" id="{{id}}" value="{{value}}" class="{{class}}" {{extra}} />',
        'number'
            => '<input type="number" name="{{name}}" id="{{id}}" value="{{value}}" class="{{class}}" {{extra}} />',
        'submit'
            => '<input type="submit" value="{{value}}" class="{{class}}" {{extra}} />',
        'multicheckbox'
            => '', //doesn't have its own uses the checkbox template multiple times
        'range'
            => '<input type="range" name="{{name}}" id="{{id}}" value="{{value}}" class="{{class}}" {{extra}} />',
        'search'
            => '<input type="search" name="{{name}}" id="{{id}}" value="{{value}}" class="{{class}}" {{extra}} />',
        'output'
            => '<output name="{{name}}" id="{{id}}" {{extra}} >{{value}}</output>',
        'optgroup'
            => '<optgroup label="{{label}}">{{options}}</optgroup>',
        'date'
            => '<input type="date" name="{{name}}" id="{{id}}" value="{{value}}" class="{{class}}" {{extra}} />',
        'datetime_local'
            => '<input type="datetime-local" name="{{name}}" id="{{id}}" value="{{value}}" class="{{class}}" {{extra}} />',
        'time'
            => '<input type="time" name="{{name}}" id="{{id}}" value="{{value}}" class="{{class}}" {{extra}} />',
        'week'
            => '<input type="week" name="{{name}}" id="{{id}}" value="{{value}}" class="{{class}}" {{extra}} />',
        'month'
            => '<input type="month" name="{{name}}" id="{{id}}" value="{{value}}" class="{{class}}" {{extra}} />'
    );


    /**
     * Create element type
     * @param $name
     * @param $type
     * @param array $properties
     * @param null $form
     */
    public function __construct($name, $type, array $properties = array(), &$form = null)
    {
        $this->type = $type;
        $this->name = $name;
        $this->properties = $properties;
        $this->form = $form;

        if ($type == 'checkbox' && !isset($properties['value'])) {
            //default checkboxes to false so that if they get unchecked in a form and don't get
            //submitted back, they will remain false which is the goal of un-checking them
            $this->properties['value'] = 0;
        }
    }

    /**
     * Set value
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        if ($this->type == 'checkbox' && (int)$value != 1) {
            //default everything that is not 1 to false
            $value = 0;
        }

        $this->properties['value'] = $value;
        return $this;
    }

    /**
     * Set Label
     * @param $label
     */
    public function setLabel($label)
    {
        $this->properties['label'] = $label;
    }

    /**
     * Add css class
     * @param $cssClass
     */
    public function addClass($cssClass)
    {
        if (!is_array($this->properties['class'])) {
            $this->properties['class'] = array();
        }
        $this->properties['class'][] = $cssClass;
    }

    /**
     * @param $attribute
     * @param null $value
     */
    public function addAttribute($attribute, $value = null)
    {
        if (!is_null($value)) {
            $this->properties['extra'][$attribute] = $value;
        } else {
            $this->properties['extra'][] = $attribute;
        }
    }

    /**
     * Get value
     * @return array|string|null
     */
    public function getValue()
    {
        return $this->getProperties('value');
    }

    /**
     * Get field properties
     * @param null $arg
     * @return array|string|null
     */
    public function getProperties($arg = null)
    {
        if (is_string($arg)) {
            return (isset($this->properties[$arg]) ? $this->properties[$arg] : null);
        }
        return $this->properties;
    }

    /**
     * Get field type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get field template
     * @return string
     */
    public function getTemplate()
    {
        return self::$templates[$this->getType()];
    }

    /**
     * Get field name
     * @param bool $text - if true, returns the name in plain text instead of the html version
     * @return string
     */
    public function getName($text = false)
    {
        if (is_object($this->form) && !$text) {

            //do not give a file element type an array name
            return $this->type == 'file' ?
                $this->getFormFileName() : "{$this->form->getName()}[$this->name]";
        }

        return $this->name;
    }

    /**
     * File fields have a diff name structure when posted back
     * @return string
     */
    public function getFormFileName()
    {
        if ($this->getType() != 'file' || !is_object($this->form)) {
            return null;
        }
        return $this->form->getName() . '_' . $this->name;
    }

    /**
     * Get field ID
     * @return string
     */
    public function getId()
    {
        return trim(preg_replace('/\W/', '_', $this->getName()), '_');
    }

    /**
     * Get field label
     * @param bool $html - if false, returns the label in plain text instead of html
     * @return string
     */
    public function getLabel($html = true)
    {
        if (!isset($this->properties['label'])) {
            //if a label is not given in the properties, try convert the element name to a human friendly label
            $this->properties['label'] = ucwords( preg_replace('/(\W|_)/', ' ', $this->getName(true)) );
        }

        return ($html ? static::html($this, 'label') : $this->properties['label']);
    }

    /**
     * Hide the fields label
     * @param bool $hide
     * @return $this
     */
    public function hideLabel($hide = true)
    {
        $this->hideLabel = $hide;
        return $this;
    }

    /**
     * Display fields errors
     * @param bool $display
     * @return $this
     */
    public function displayErrors($display = true)
    {
        $this->hideErrors = !$display;
        return $this;
    }

    /**
     * Test if the field label is hidden
     * @return bool
     */
    public function labelHidden()
    {
        return $this->hideLabel;
    }

    /**
     * Get the actual html element
     * @return string
     */
    public function getField()
    {
        return static::html($this, 'field');
    }

    /**
     * Get this elements errors
     * @param bool $html - if false, returns the errors as array instead of html
     * @return array|string
     */
    public function getErrors($html = true)
    {
        return ($html ? static::html($this, 'errors') : $this->errors);
    }

    /**
     * Render this element or part of it
     * @param string $part - part of the lement to render [all|label|field|errors]
     * @param string $wrapper
     * @return string
     */
    public function render($part = 'all', $wrapper = 'div')
    {
        return static::html($this, $part, $wrapper);
    }

    /**
     * Add error message to this field
     * @param $error
     * @return $this
     */
    public function addErrorMessage($error)
    {
        array_push($this->errors, $error);
        return $this;
    }

    /**
     * If this field is linked to a form and validate is defined,
     * this will return true/false otherwise null
     * @return null|bool
     */
    public function isValid()
    {
        if (!is_null($this->form)) {
            return $this->form->validate($this->getName(true), $this->getValue());
        }
        return null;
    }

    /**
     * HTML renderer
     * @param Element $element
     * @param string $part [all|label|field|errors]
     * @param string $wrapper
     * @return string
     */
    protected static function html(Element &$element, $part = 'all', $wrapper = 'div')
    {
        $label = $element->getLabel(false);
        $id = $element->getId();

        //label
        if ($part == 'all' || $part == 'label') {

            $labelHtml = self::$templates['label'];
            $labelHtml = str_replace('{{id}}', $id, $labelHtml);
            $labelHtml = str_replace('{{text}}', $label, $labelHtml);

            if ($part == 'label') {
                return $labelHtml;
            }
        }

        //element itself
        if ($part == 'all' || $part == 'field') {

            $type = $element->getType();
            $fieldHtml = $element->getTemplate();

            //do element specific stuff
            if (is_callable("static::$type")) {

                //elements that have specific requirements have their own methods
                static::$type($element, $fieldHtml);
            } else {
                //the text based elements can all be handled by the text method.
                //to provide custom functionality for one, simply define a static method for it
                static::text($element, $fieldHtml);
            }

            //the hidden field type should never need the stuff below, might as well return here
			if ($part == 'field' || $element->getType() == 'hidden') {
                return $fieldHtml;
            }
		}

        //errors
        if ($part == 'all' || $part == 'errors') {

            $errors = $element->getErrors(false);
            $errorsHtml = '';
            if (is_array($errors) && !empty($errors)) {

                $errorsHtml = '<ul class="error-list">';
                foreach ($errors as $error) {
                    $errorsHtml .= sprintf('<li>%s</li>', $error);
                }
                $errorsHtml .= '</ul>';
            }

            if ($part == 'errors') {
                return $errorsHtml;
            }
        }

        //all together now!
        $html = '<%6$s id="%1$s_wrapper" class="%2$s-element"> %3$s %4$s %5$s </%6$s>';

        if ($element->labelHidden() || in_array($element->getType(), array('hidden', 'submit'))) {
            $labelHtml = ''; //label not required/wanted
        }

        return sprintf(
            $html,
            $id,
            str_replace('_', '-', (is_object($element->form) ? $element->form->getName():'form') ),
            ($type == 'checkbox' ? $fieldHtml : $labelHtml), //swap these around for checkbox fields
            ($type == 'checkbox' ? $labelHtml : $fieldHtml),
            ($element->hideErrors ? '' : $errorsHtml),
            $wrapper
        );
    }

    /**
     * Text element type,
     * also caters to other text based element types
     * @param Element $element
     * @param $fieldHtml
     */
    protected static function text(Element &$element, &$fieldHtml)
    {
        //replace general placeholders if not already done
        $fieldHtml = str_replace('{{id}}', $element->getId(), $fieldHtml);
        $fieldHtml = str_replace('{{name}}', $element->getName(), $fieldHtml);
        $fieldHtml = str_replace(
            '{{value}}',
            (is_array($element->getValue()) ? 'array!' : $element->getValue()),
            $fieldHtml
        );

        //classes
        $class = $element->getProperties('class');
        if (!is_array($class)) {
            $class = array();
        }
        $fieldHtml = str_replace('{{class}}', implode(' ', $class), $fieldHtml);

        //extra attributes if any
        $extra = $element->getProperties('extra');
        $extraAttributes = '';
        if (is_array($extra)) {
            foreach ($extra as $attr => $val) {
                if (is_string($attr)) {
                    $extraAttributes .= sprintf('%s="%s" ', $attr, $val);
                } else {
                    $extraAttributes .= sprintf('%s ', $val);
                }
            }
        }

        $fieldHtml = str_replace('{{extra}}', $extraAttributes, $fieldHtml);
    }

    /**
     * File element type
     * @param Element $element
     * @param $fieldHtml
     */
    protected static function file(Element &$element, &$fieldHtml)
    {
        $value = $element->getValue();
        if (!empty($value)) {
            $value = '<a href="'.$value.'" target="_blank">view</a>';
        }
        $fieldHtml = str_replace('{{value}}', $value, $fieldHtml);

        static::text($element, $fieldHtml);
    }

    /**
     * Checkbox element type
     * @param Element $element
     * @param $fieldHtml
     */
    protected static function checkbox(Element &$element, &$fieldHtml)
    {
        $value = $element->getValue();
        $value = 'value="1" ' . ($value == 1 ? 'checked="checked"' : '');
        $fieldHtml = str_replace('{{value}}', $value, $fieldHtml);

        static::text($element, $fieldHtml);
    }

    /**
     * Select element type
     * @param Element $element
     * @param $fieldHtml
     * @throws \Exception
     */
    protected static function select(Element &$element, &$fieldHtml)
    {
        $value = $element->getValue();
        $options = $element->getProperties('options');
        $extra = $element->getProperties('extra');

        if (!is_array($options)) {
            throw new \Exception("Options for field type '{$element->getType()}' must be given as array.");
        }

        //this field can be used as a multiselect, so present values as array
        if (!is_array($value)) {
            $value = array($value);
        }

        if (isset($extra['multiple'])) {
            $fieldHtml = str_replace('{{name}}', "{$element->getName()}[]", $fieldHtml);
        }

        foreach ($options as $option => $label) {

            if (is_array($label)) {

                //we have an opt-group!
                $optionGroup = array();
                foreach ($label as $o => $lbl) {
                    $optionGroup[$o] = sprintf(
                        '<option value="%1$s" %2$s>%3$s</option>',
                        $o,
                        (in_array($o, $value) ? 'selected="selected"' : ''),
                        $lbl
                    );
                }
                $groupHtml = self::$templates['optgroup'];
                $groupHtml = str_replace('{{label}}', $option, $groupHtml);
                $groupHtml = str_replace('{{options}}', implode('', $optionGroup), $groupHtml);
                $options[$option] = $groupHtml;

                continue;
            }

            $options[$option] = sprintf(
                '<option value="%1$s" %2$s>%3$s</option>',
                $option,
                (in_array($option, $value) ? 'selected="selected"' : ''),
                $label
            );
        }

        $fieldHtml = str_replace('{{value}}', implode('', $options), $fieldHtml);
        static::text($element, $fieldHtml);
    }

    /**
     * Radio element type
     * @param Element $element
     * @param $fieldHtml
     * @throws \Exception
     */
    protected static function radio(Element &$element, &$fieldHtml)
    {
        $name = $element->getName();
        $id = $element->getId();

        $value = $element->getValue();
        if ($element->getType() == 'multicheckbox' && !is_array($value)) {
            $value = array();
        }

        $options = $element->getProperties('options');
        if (!is_array($options)) {
            throw new \Exception("Options for field type '{$element->getType()}' must be given as array.");
        }

        //classes
        $class = $element->getProperties('class');
        if (!is_array($class)) {
            $class = array();
        }

        $extra = $element->getProperties('extra');
        $extraAttributes = '';
        if (is_array($extra)) {
            foreach ($extra as $attr => $val) {
                if (is_string($attr)) {
                    $extraAttributes .= sprintf('%s="%s" ', $attr, $val);
                } else {
                    $extraAttributes .= sprintf('%s ', $val);
                }
            }
        }

        foreach ($options as $option => $lbl) {

            $labelHtml = self::$templates['label'];
            $labelHtml = str_replace('{{id}}', "{$id}_{$option}", $labelHtml);
            $labelHtml = str_replace('{{text}}', $lbl, $labelHtml);

            if ($element->getType() == 'radio') {

                $field = self::$templates['radio'];
                $field = str_replace('{{name}}', $name, $field);
                $field = str_replace(
                    '{{value}}', ( 'value="'.$option.'" ' . ($option == $value ? ' checked="checked"':'') ), $field
                );

            } else if ($element->getType() == 'multicheckbox') {

                $field = self::$templates['checkbox'];
                $field = str_replace('{{name}}', "{$name}[]", $field);
                $field = str_replace(
                    '{{value}}',
                    ( 'value="'.$option.'" ' . (in_array($option, $value) ? ' checked="checked"':'') ),
                    $field
                );
            }

            $field = str_replace('{{id}}', "{$id}_{$option}", $field);
            $field = str_replace('{{class}}', implode(' ', $class), $field);
            $field = str_replace('{{extra}}', $extraAttributes, $field);
            $field .= ' '.$labelHtml;

            $options[$option] = "<div>$field</div>";
        }

        $fieldHtml = implode('', $options);
    }

    /**
     * Multi-Checkbox element type - custom type
     * @param Element $element
     * @param $fieldHtml
     */
    protected static function multicheckbox(Element &$element, &$fieldHtml)
    {
        static::radio($element, $fieldHtml);
    }

    /**
     * Get settings parsed via properties
     * @param  string $name setting name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getProperties($name);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
} 