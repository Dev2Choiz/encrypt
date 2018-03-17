<?php
// Assetic Configuration
$preset = $container->getParameter('variablesCss');
$container->loadFromExtension('assetic', array(
    'filters' => array(
        'cssrewrite' => null,
        'lessphp' => array(
            'apply_to' => '\.less$',
            'preserve_comments' => false,
            'presets' => $preset
        ),
    ),
));
