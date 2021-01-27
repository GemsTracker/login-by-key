# Login by key

Gemstracker module to enable login by magic key through mail

**Requires** PHP 7.0+

## Intallation
1. Add to composer.json of project, including adding the repository
2. composer update
3. Register your module in your Projects Escort by adding the following static property:
```PHP
    public static $modules = [
        'LoginByKey' => \LoginByKey\ModuleSettings::class,
    ];
```

### Database patches
- Database patches for changing the gems__user_passwords table have been added to patch level 66


### Extended classes

Extra warning: the following Gemstracker classes have been extended.
Your project should extend these, instead of the Gems or MUtil version if they exists in your project.
- Mail\\MailLoader : Adds the UserLoginKeyMailer as extra mailer. Extend this mailer, or add to your mailloader.
- User\\User : Adds the CanLoginByKey trait, giving the option to set this per user type. Currently it only returns true.
  Extend LoginByKey/User/User or add the CanLoginByKey trait.
- TwoFactorCheckSnippet : Disables the cancel button on child menu use. Extend this class or add an empty getMenuList method.
