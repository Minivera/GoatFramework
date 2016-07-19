# Hw to install an addon

To install an addon, download the full folder and copy it at the root of 
the framework files. The system is currently hardcoded to look for the generated 
and data folder if needed.

To ensure the addons work, you NEED to use the dependency engine by creating class this way :

$container = Core\Engines\DependencyEngine::getInstance();
$container->set(FULL_FORMED_CLASSNAME)->create(ARRAY_OF_ARGUMENTS);
