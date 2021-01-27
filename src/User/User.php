<?php

namespace LoginByKey\User;


class User extends \Gems_User_User
{
    use CanLoginByKey;
}
