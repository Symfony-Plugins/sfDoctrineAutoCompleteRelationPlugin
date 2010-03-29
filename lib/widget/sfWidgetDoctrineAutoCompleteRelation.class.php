<?php

/*
 * This file is part of the sfWidgetDoctrineAutoCompleteRelation package.
 * (c) Gregory Schurgast <greg@negko.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetDoctrineAutoCompleteRelation represents a autocomplete Relation manager.
 *
 * This widget needs some JavaScript to work. So, you need to include the JavaScripts
 * files returned by the getJavaScripts() method.
 *
 *
 * @package    symfony
 * @subpackage widget
 * @author     Gregory Schurgast <greg@negko.com>
 */
class sfWidgetDoctrineAutoCompleteRelation extends sfWidgetForm
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * choices:            An array of possible choices (required)
   *  * class:              The main class of the widget
   *  * class_select:       The main class of the widget
   *  * label_unassociated: The label for unassociated
   *  * label_associated:   The label for associated
   *  * unassociate:        The HTML for the unassociate link
   *  * associate:          The HTML for the associate link
   *  * template:           The HTML template to use to render this widget
   *                        The available placeholders are:
   *                          * label_associated
   *                          * label_unassociated
   *                          * associate
   *                          * unassociate
   *                          * associated
   *                          * unassociated
   *                          * class
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('choices');

    $this->addOption('class', 'doctrineautocompleterelation');
    $this->addOption('class_select', 'doctrineautocompleterelation_select');
    $this->addOption('label_unassociated', 'Unassociated');
    $this->addOption('label_associated', 'Associated');
    $this->addOption('unassociate', '<img src="/sfFormExtraPlugin/images/next.png" alt="unassociate" />');
    $this->addOption('associate', '<img src="/sfFormExtraPlugin/images/previous.png" alt="associate" />');
    $this->addOption('template', <<<EOF
<div class="%class%">
    <div><input name="doctrineautocompleterelation-unassociated" id="doctrineautocompleterelation-unassociated" /></div>
    <div>
        <span>%label_associated%</span>
        <ul class="doctrineautocompleterelation-associated">
            %associated%
        </ul>
    </div>
    %associatedInput%
    
  <script type="text/javascript">
    var available = %available%
    
    $(document).ready(function(){
        $('.doctrineautocompleterelation-associated li').hover(
            function(){
                $(this).css({ background : '#FFFFEE'});
            },
            function(){
                $(this).css({ background : '#FFFFFF'});
            }
        );
        
        $('.doctrineautocompleterelation-associated .remove').click(function(e){
            e.preventDefault();
            
            if (confirm('Are you sure you want to remove the relation') ) {
                val = $(this).attr('id').replace('option','');
                option = $('#%id% option[value='+val+']');
                $('#%id% option[value='+val+']').remove();            
                $(this).closest('li').fadeOut();
            }
        });
    })
  </script>
</div>
EOF
);
  }

  /**
   * Renders the widget.
   *
   * @param  string $name        The element name
   * @param  string $value       The value selected in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if (is_null($value))
    {
      $value = array();
    }

    $choices = $this->getOption('choices');
    if ($choices instanceof sfCallable)
    {
      $choices = $choices->call();
    }

    $associated = array();
    $unassociated = array();
    foreach ($choices as $key => $option)
    {
      if (in_array(strval($key), $value))
      {
        $associated[$key] = $option;
      }
      /*else
      {
        $unassociated[$key] = $option;
      }*/
    }
    $list = '';
    $i = 1;
    foreach($associated as $k => $elt){ 
        $lastclass = $i == count($associated) ? ' class="last"' : '';
        $list .= '<li'.$lastclass.'><a href="" class="remove" id="option'.$k.'">Delete</a>'.$elt.'</li>';
        $i++;
    }

    $size = isset($attributes['size']) ? $attributes['size'] : (isset($this->attributes['size']) ? $this->attributes['size'] : 10);

    $associatedWidget = new sfWidgetFormSelect(array('multiple' => true, 'choices' => $associated), array('size' => $size, 'class' => $this->getOption('class_select')));
    //$unassociatedWidget = new sfWidgetFormSelect(array('multiple' => true, 'choices' => $unassociated), array('size' => $size, 'class' => $this->getOption('class_select')));

    return strtr($this->getOption('template'), array(
      '%class%'              => $this->getOption('class'),
      '%class_select%'       => $this->getOption('class_select'),
      '%id%'                 => $this->generateId($name),
      '%label_associated%'   => $this->getOption('label_associated'),
      '%associated%'         => $list,
      '%available%'          => json_encode($choices),
      '%associatedInput%'    => $associatedWidget->render($name),
    ));
  }

  /**
   * Gets the JavaScript paths associated with the widget.
   *
   * @return array An array of JavaScript paths
   */
  public function getJavascripts()
  {
    return array('/sfDoctrineAutoCompleteRelationPlugin/js/jquery.autocompleter.js');
  }
  
    /**
   * Gets the StyleSheet paths associated with the widget.
   *
   * @return array An array of StyleSheet paths
   */
  public function getStylesheets()
  {
    return array('/sfDoctrineAutoCompleteRelationPlugin/css/sfWidgetDoctrineAutoCompleteRelation.css' => 'screen');
  }

  public function __clone()
  {
    if ($this->getOption('choices') instanceof sfCallable)
    {
      $callable = $this->getOption('choices')->getCallable();
      if (is_array($callable))
      {
        $callable[0] = $this;
        $this->setOption('choices', new sfCallable($callable));
      }
    }
  }
}
