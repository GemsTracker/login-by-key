# Login by key

Gemstracker module to enable login by magic key through mail

## Intallation
1. Add to composer.json of project, including adding the repository
2. composer update
3. Register your module in your Projects Escort by adding the following static property:
```PHP
    public static $modules = [
        'LoginByKey' => \LoginByKey\ModuleSettings::class,
    ];
```

## New Modules
- Copy src/ModuleSettings.php to your new module.
- Change the namespace and the property 'moduleName' in ModuleSettings.
- Copy src/ModuleSubscriber.php to your module.
- ModuleSubscriber has some examples of different Events and can be used
