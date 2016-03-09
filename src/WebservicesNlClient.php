<?php

require_once 'WebservicesNlSoapClient.php';

/*
        All services offered by Webservices.nl are available as methods in this class.
        The methods __getServerState and __setServerState can be used to prevent timeouts when a server is unavailable.
        
        All parameters are expected to be in UTF-8 encoding, output is in UTF-8 as well.

        For documentation see:
            https://ws1.webservices.nl/documentation

        This client has been tested on PHP 5.2.
        
        Any questions, remarks, bugs?
            - tech@webservices.nl
            - support.webservices.nl

 */
class WebservicesNlClient
{
    private $_client;

    /*
        Specify $username and $password to override any preconfigured credentials in config.php
     */
    public function __construct($username = '', $password = '')
    {
        if (empty($username) || empty($password)) {
            @include dirname(__FILE__) . '/config.php';
            if (isset($webservicesCredentials['username'], $webservicesCredentials['password'])) {
                $username = $webservicesCredentials['username'];
                $password = $webservicesCredentials['password'];
            }
        }

        $this->_initialize($username, $password);
    }

    private function _initialize($username, $password)
    {
        $this->_client = new WebservicesNlSoapClient(
            WebservicesNlSoapClient::SERVER_WS1,
            [
                'ws_username' => $username,
                'ws_password' => $password,
                'ws_response_timeout' => 20,
                'encoding' => 'UTF-8',
            ]
        );
    }

    public function __getClient()
    {
        return $this->_client;
    }

    /*
        Calling this function after calling the constructor prevents unnecessary timeouts
        in case one of the server's is temporarily unavailable.

         $state must be a string returned from ws_getServerState()
     */
    public function __setServerState($state)
    {
        return $this->_client->ws_setServerState($state);
    }

    /*
        Returns a string containing information on what servers are available.
        Call ws_setServerState() with this string to restore
    */
    public function __getServerState()
    {
        return $this->_client->ws_getServerState();
    }

    /*
        Retrieve a token with which a new account may be registered via the <Webview Interface> by one of your customers.
        The newly created account will be associated with your account. Tokens are only valid for a limited amount of time.
        
        Use <accountGetCreationStatus> to get the id of the account created using the token.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.accountGetCreationToken
    
        Parameters:
            string $return_url - This is the URL to which the customer is redirected after registering a Webservices.nl account.
                    If a customer arrives at this URL the <accountGetCreationStatus> should be called to check if account creation
                    was successful.
    

        Returns:
            An <AccountCreationToken>
    */
    public function accountGetCreationToken($return_url)
    {
        return $this->_client->accountGetCreationToken(['return_url' => $return_url]);
    }

    /*
        Get the id of an account created with a token from <accountGetCreationToken>.
        
        Depending on the outcome of the account registration the following is returned:
        A value larger than 0 - The customer has successfully registered an account.
        The returned value is the account id of the new account.
        A value of 0 - The customer has not yet finished the account registration process. It may be that Webservices.nl is
        awaiting confirmation of a payment performed by the customer. You should *not* retrieve a new account registration token,
        or direct the customer to the account registration page again. This could result in the customer registering and paying for
        an account that is never used. Instead, try calling <accountGetCreationStatus> again later.
        A 'Server.Data.NotFound' error - This error indicates that the registration process was unsuccesful.
        See <Error Handling::Error codes>. You may start the registration process over by calling <accountGetCreationToken>.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.accountGetCreationStatus
    
        Parameters:
            string $token - A token retrieved using <accountGetCreationToken>
    

        Returns:
            The account id, which is 0 when the account registration has not finished yet
    */
    public function accountGetCreationStatus($token)
    {
        return $this->_client->accountGetCreationStatus(['token' => $token]);
    }

    /*
        Retrieve a token that can be used order account balance via the <Webview Interface>.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.accountGetOrderToken
    
        Parameters:
            int $accountid - The id of the account for which balance will be ordered
            string $return_url - This is the URL to which the customer is redirected after finishing the order process.
    

        Returns:
            An <AccountOrderToken>
    */
    public function accountGetOrderToken($accountid, $return_url)
    {
        return $this->_client->accountGetOrderToken(['accountid' => $accountid, 'return_url' => $return_url]);
    }

    /*
        Remove all or one <User::Session> of a <User>.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userSessionRemove
    
        Parameters:
            int $userid - User ID of the user to view, use 0 for the current user
            string $reactid - Session ID to remove, use 0 to remove all sessions
    

        Returns:
            Nothing
    */
    public function userSessionRemove($userid, $reactid)
    {
        return $this->_client->userSessionRemove(['userid' => $userid, 'reactid' => $reactid]);
    }

    /*
        Lists all the current valid <User::Sessions> of a <User>.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userSessionList
    
        Parameters:
            int $userid - User ID of the user to view, use 0 for the current user
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <Session> entries.
    */
    public function userSessionList($userid, $page)
    {
        return $this->_client->userSessionList(['userid' => $userid, 'page' => $page]);
    }

    /*
        Returns the users balance.
        
        If the user is in the 'autoassign' user group, he is not restricted by his balance. In that case,
        he can still do method calls even though his balance amount is zero. If the user is not in
        the 'autoassign' user group, the user can spend his own balance amount, but not more.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userViewBalance
    
        Parameters:
            int $userid - User ID of the user to view the balance of, use 0 for the current user
    

        Returns:
            balance - The users balance
    */
    public function userViewBalance($userid)
    {
        return $this->_client->userViewBalance(['userid' => $userid]);
    }

    /*
        Change the user's balance.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userEditBalance
    
        Parameters:
            int $userid - User ID of the user to edit the balance of, use 0 for the current user
            float $balance - Amount of balance to add to (or remove from, if negative) the user
    

        Returns:
            Nothing
    */
    public function userEditBalance($userid, $balance)
    {
        return $this->_client->userEditBalance(['userid' => $userid, 'balance' => $balance]);
    }

    /*
        Returns the accounts balance
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.accountViewBalance
    
        Parameters:
            int $accountid - Account ID of the account to view the balance of, use 0 for the current account
    

        Returns:
            balance - The accounts balance
    */
    public function accountViewBalance($accountid)
    {
        return $this->_client->accountViewBalance(['accountid' => $accountid]);
    }

    /*
        View the profile of a user.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userViewV2
    
        Parameters:
            int $userid - User ID of the user to view, use 0 for the current user
    

        Returns:
            out - A <UserV2> structure
    */
    public function userViewV2($userid)
    {
        return $this->_client->userViewV2(['userid' => $userid]);
    }

    /*
        Edit the profile of a user. This method allows <Group::Account users>
        to edit their own profile. <Group::Account admins> can use <userEditExtendedV2>
        to change the complete profile.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userEditV2
    
        Parameters:
            int $userid - User ID of the user to edit, use 0 for the current user.
            string $email - Registration email address, used for activation.
            string $address - Address of the company using this user.
            string $contactname - Name of the contact person responsible for this user.
            string $contactemail - This field is not used and is ignored by the method.
            string $telephone - Telephone number of the contact person responsible for this user.
            string $fax - Fax number of the contact person responsible for this user.
            string $password - The current password for this user.
    

        Returns:
            Nothing
    */
    public function userEditV2($userid, $email, $address, $contactname, $contactemail, $telephone, $fax, $password)
    {
        return $this->_client->userEditV2(['userid' => $userid, 'email' => $email, 'address' => $address, 'contactname' => $contactname, 'contactemail' => $contactemail, 'telephone' => $telephone, 'fax' => $fax, 'password' => $password]);
    }

    /*
        Edit the complete profile of a user. This method is only available to
        <Group::Account admins>. <Group::Account users> can use <userEditV2>
        to change some part of the profile.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userEditExtendedV2
    
        Parameters:
            int $userid - User ID of the user to edit, use 0 for the current user
            string $nickname - Nickname to use for this user. All users get a
                    prefix set by <Account::Username prefix>.
            string $password - The new password for this user. To keep the current password pass the empty string.
            string $email - Registration email address, used for activation.
            string $companyname - Name of the company using this user, if any.
            string $address - Address of the company using this user, if any.
            string $contactname - Name of the contact person responsible for this user.
            string $contactemail - This field is not used and is ignored by the method.
            string $telephone - Telephone number of the contact person responsible for this user.
            string $fax - Fax number of the contact person responsible for this user.
            string $clientcode - Deprecated, shoud contain an empy string.
            string $comments - Comments on the user, can only be seen and edited by <Group::Account admins>.
            int $accountid - the Account ID to assign this user to, use 0 for the current account. Only usable by <Group::Admins>
            float $balancethreshold - Balance threshold to alert user, 0 to disable.
            string $notificationrecipients - Recipients of balance alert notification: 'accountcontact' = contact account contact, 'user' = contact user, 'accountcontact_and_user' = both
    

        Returns:
            Nothing
    */
    public function userEditExtendedV2($userid, $nickname, $password, $email, $companyname, $address, $contactname, $contactemail, $telephone, $fax, $clientcode, $comments, $accountid, $balancethreshold, $notificationrecipients)
    {
        return $this->_client->userEditExtendedV2(['userid' => $userid, 'nickname' => $nickname, 'password' => $password, 'email' => $email, 'companyname' => $companyname, 'address' => $address, 'contactname' => $contactname, 'contactemail' => $contactemail, 'telephone' => $telephone, 'fax' => $fax, 'clientcode' => $clientcode, 'comments' => $comments, 'accountid' => $accountid, 'balancethreshold' => $balancethreshold, 'notificationrecipients' => $notificationrecipients]);
    }

    /*
        Create a user, assign it to groups and send it an activation mail.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userCreateV2
    
        Parameters:
            int $accountid - the Account ID to assign this user to.
            string $nickname - Nickname to use for this user, leave empty to to
                    create a random nickname. All users get a
                    prefix set by <Account::Username prefix>.
            string $password - Password to use for authentication, leave empty for a strong random password.
            intArray $usergroups - array of user group IDs to assign this user to. see <userListAssignableGroups> for a list.
            string $email - Registration email address, used for activation.
            string $companyname - Name of the company using this user, if any.
            string $address - Address of the company using this user, if any.
            string $contactname - Name of the contact person responsible for this user.
            string $contactemail - This field is not used and is ignored by the method.
            string $telephone - Telephone number of the contact person responsible for this user.
            string $fax - Fax number of the contact person responsible for this user.
            string $clientcode - Deprecated, should contain an empty string.
            string $comments - Comments on the user, can only be seen and edited by <Group::Account admins>.
    

        Returns:
            id         - User ID of the newly created user.
            nickname   - Nickname of the newly created user.
            password   - Password of the newly created user. Together with the activation email, this is the only time the password is plainly visible.
    */
    public function userCreateV2($accountid, $nickname, $password, $usergroups, $email, $companyname, $address, $contactname, $contactemail, $telephone, $fax, $clientcode, $comments)
    {
        return $this->_client->userCreateV2(['accountid' => $accountid, 'nickname' => $nickname, 'password' => $password, 'usergroups' => $usergroups, 'email' => $email, 'companyname' => $companyname, 'address' => $address, 'contactname' => $contactname, 'contactemail' => $contactemail, 'telephone' => $telephone, 'fax' => $fax, 'clientcode' => $clientcode, 'comments' => $comments]);
    }

    public function createTestUser($application, $email, $companyname, $contactname, $telephone)
    {
        return $this->_client->createTestUser(['application' => $application, 'email' => $email, 'companyname' => $companyname, 'contactname' => $contactname, 'telephone' => $telephone]);
    }

    /*
        Change the current password of a user. A <Group::Account users>
        has to give the old password for authentication, <Group::Account admins>
        do not have to.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userChangePassword
    
        Parameters:
            int $userid - User ID of the user to edit, use 0 for the current user
            string $old_password - The old password, not required for <Group::Account admins>
            string $new_password - The new password.
    

        Returns:
            Nothing
    */
    public function userChangePassword($userid, $old_password, $new_password)
    {
        return $this->_client->userChangePassword(['userid' => $userid, 'old_password' => $old_password, 'new_password' => $new_password]);
    }

    /*
        Remove the user. This method is only available to
        <Group::Account admins>.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userRemove
    
        Parameters:
            int $userid - User ID of the user to remove, use 0 for the current user
    

        Returns:
            Nothing
    */
    public function userRemove($userid)
    {
        return $this->_client->userRemove(['userid' => $userid]);
    }

    /*
        Send a notification email to a user with a new password. This
        method is part of the <User::Creation> process.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userNotify
    
        Parameters:
            int $userid - User ID of the user to notify, use 0 for the current user
            string $password - Password to use for authentication, leave empty for a strong random password.
    

        Returns:
            out - Password of the user. Together with the activation email, this is the only time the password is plainly visible.
    */
    public function userNotify($userid, $password)
    {
        return $this->_client->userNotify(['userid' => $userid, 'password' => $password]);
    }

    /*
        List all groups that the current user can assign to the target user.
        This list contains both assigned and unassigned groups.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userListAssignableGroups
    
        Parameters:
            int $userid - User ID of the user to target, use 0 for the current user
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <User> entries.
    */
    public function userListAssignableGroups($userid, $page)
    {
        return $this->_client->userListAssignableGroups(['userid' => $userid, 'page' => $page]);
    }

    /*
        Add a user to a group. A user can use <userListAssignableGroups>
        to view the groups that can be assigned.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userAddGroup
    
        Parameters:
            int $userid - User ID of the user to add to the group, use 0 for the current user
            int $usergroupid - User Group ID of the group to add the user to
    

        Returns:
            Nothing
    */
    public function userAddGroup($userid, $usergroupid)
    {
        return $this->_client->userAddGroup(['userid' => $userid, 'usergroupid' => $usergroupid]);
    }

    /*
        Remove a user from a group. A user can use <userViewV2>
        to view the groups that are currently assigned to the user.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userRemoveGroup
    
        Parameters:
            int $userid - User ID of the user to remove from the group, use 0 for the current user
            int $usergroupid - User Group ID of the group to remove the user from
    

        Returns:
            Nothing
    */
    public function userRemoveGroup($userid, $usergroupid)
    {
        return $this->_client->userRemoveGroup(['userid' => $userid, 'usergroupid' => $usergroupid]);
    }

    /*
        View the profile of an account.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.accountViewV2
    
        Parameters:
            int $accountid - Account ID of the account to move use 0 for the account
                    of the current user
    

        Returns:
            out - A <AccountV2> structure
    */
    public function accountViewV2($accountid)
    {
        return $this->_client->accountViewV2(['accountid' => $accountid]);
    }

    /*
        Edit the properties (<AccountV2>) of an account. This method allows <Group::Account admins>
        to edit their account profile.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.accountEditV2
    
        Parameters:
            int $accountid - Account ID of the account to edit, use 0 for the current user's account
            string $address - Address of the company using this account.
            string $contactname - Name of the contact person responsible for this account.
            string $contactemail - Email address of the contact person responsible for this account.
            string $telephone - Telephone number of the contact person responsible for this account.
            string $fax - Fax number of the contact person responsible for this account.
            string $description - Description of the account to its users.
            float $balancethreshold - Balance threshold to alert account, use 0 to disable.
    

        Returns:
            Nothing
    */
    public function accountEditV2($accountid, $address, $contactname, $contactemail, $telephone, $fax, $description, $balancethreshold)
    {
        return $this->_client->accountEditV2(['accountid' => $accountid, 'address' => $address, 'contactname' => $contactname, 'contactemail' => $contactemail, 'telephone' => $telephone, 'fax' => $fax, 'description' => $description, 'balancethreshold' => $balancethreshold]);
    }

    /*
        List all users in this account. This method is only available to
        <Group::Account admins>.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.accountUserListV2
    
        Parameters:
            int $accountid - Account ID of the account to list, use 0 for the current user's account
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <UserV2> entries.
    */
    public function accountUserListV2($accountid, $page)
    {
        return $this->_client->accountUserListV2(['accountid' => $accountid, 'page' => $page]);
    }

    /*
        Search for users of an account using a search phrase. This method is
        only available to <Group::Account admins>.
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.accountUserSearchV2
    
        Parameters:
            int $accountid - Account ID of the account to list, use 0 for the current user's account
            string $phrase - Phrase to search for in user profiles
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <UserV2> entries.
    */
    public function accountUserSearchV2($accountid, $phrase, $page)
    {
        return $this->_client->accountUserSearchV2(['accountid' => $accountid, 'phrase' => $phrase, 'page' => $page]);
    }

    /*
        Set host restrictions for the account
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.accountEditHostRestrictions
    
        Parameters:
            int $accountid - Account ID of the account, use 0 for the current user's account
            string $restrictions - A string with host restrictions separated by semi colons (;)
    

        Returns:
            Nothing
    */
    public function accountEditHostRestrictions($accountid, $restrictions)
    {
        return $this->_client->accountEditHostRestrictions(['accountid' => $accountid, 'restrictions' => $restrictions]);
    }

    /*
        View host restrictions for the account
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.accountViewHostRestrictions
    
        Parameters:
            int $accountid - Account ID of the account, use 0 for the current user's account
    

        Returns:
            A string containing all restrictions, separated by  semi colons
    */
    public function accountViewHostRestrictions($accountid)
    {
        return $this->_client->accountViewHostRestrictions(['accountid' => $accountid]);
    }

    /*
        Set host restrictions for the user
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userEditHostRestrictions
    
        Parameters:
            int $userid - User ID of the user, use 0 for the current user
            string $restrictions - A string with host restrictions separated by semi colons (;)
    

        Returns:
            Nothing
    */
    public function userEditHostRestrictions($userid, $restrictions)
    {
        return $this->_client->userEditHostRestrictions(['userid' => $userid, 'restrictions' => $restrictions]);
    }

    /*
        View host restrictions for the user
    
        http://webview.webservices.nl/documentation/files/service_accounting-class-php.html#Accounting.userViewHostRestrictions
    
        Parameters:
            int $userid - User ID of the user, use 0 for the current user
    

        Returns:
            A string containing all restrictions, separated by  semi colons
    */
    public function userViewHostRestrictions($userid)
    {
        return $this->_client->userViewHostRestrictions(['userid' => $userid]);
    }

    /*
        Determine if a specific address exists using the unique '1234AA12'
        postcode + house number format. If returns either the address in
        <PCReeks> format, or an error if no matching address exists.
        If you want to validate an address not using this unique identifier,
        use <addressReeksAddressSearch> or <addressReeksFullParameterSearch>.
    
        http://webview.webservices.nl/documentation/files/service_address-class-php.html#Address.addressReeksPostcodeSearch
    
        Parameters:
            string $address - Address to validate using the unique '1234AA12' postcode
                    house number format.
    

        Returns:
            out - A <PCReeks> structure.
    */
    public function addressReeksPostcodeSearch($address)
    {
        return $this->_client->addressReeksPostcodeSearch(['address' => $address]);
    }

    /*
        Search for a specific address, where the address contains the
        street, house number and house number addition concatenated.
        This is useful if the house number is not stored separate from the
        street name.
        
        A number of <RangeAddress> entries is returned.
        The street names in the result may not exactly match the street in the request. To account
        for different writing styles and spelling errors, streets which match approximately are also
        returned. E.g. "Calverstraat, Amsterdam" will return an address for the "Kalverstraat".
        The results are ordered on how well they match, with the best matches first.
        
        If the given house number does not exist in the postcode range, the house number field is left empty.
        In this case, the <RangeAddress> contains a <PCReeks> which matches
        the street, but it contains no house number or house number addition. For example, searching
        for "Dam 44, Amsterdam" returns the <PCReeks> for the Dam, but the result omits
        the house number since there is no house number 44 on the Dam.
    
        http://webview.webservices.nl/documentation/files/service_address-class-php.html#Address.addressReeksAddressSearch
    
        Parameters:
            string $address - Street, house number and house number addition of the searched address. Required.
            string $postcode - Postcode in 1234AA format. Optional.
            string $city - Phrase used to select the city of the address, see <PCReeks>.plaatsnaam. Optional.
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <RangeAddress> entries.
    */
    public function addressReeksAddressSearch($address, $postcode, $city, $page)
    {
        return $this->_client->addressReeksAddressSearch(['address' => $address, 'postcode' => $postcode, 'city' => $city, 'page' => $page]);
    }

    /*
        Search for addresses in the <PCReeks> format, using different search
        phrases for each address part.
    
        http://webview.webservices.nl/documentation/files/service_address-class-php.html#Address.addressReeksFullParameterSearch
    
        Parameters:
            string $province - Phrase to search for in province name, or code of the province. See <PCReeks>.provincienaam and <PCReeks>.provinciecode
            string $district - Phrase used to select the municipality of the address, see <PCReeks>.gemeentenaam
            string $city - Phrase used to select the city of the address, see <PCReeks>.plaatsnaam
            string $street - Phrase used to select the street of the address, see <PCReeks>.straatnaam
            int $houseNo - Number used to select the house number of the address, see <PCReeks>.huisnr_van
            string $houseNoAddition - Phrase used to select the house number addition of the address
            string $nbcode - Number used to select the neighborhoodcode of the address, the first four numbers of the postcode. See <PCReeks>.wijkcode
            string $lettercombination - Phrase used to select the lettercombination of the address, the last two letters of the postcode. See <PCReeks>.lettercombinatie
            string $addresstype - Phrase used to select the addresstype of the address, see <PCReeks>.reeksindicatie
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <PCReeksSearchPartsPagedResult>.
    */
    public function addressReeksFullParameterSearch($province, $district, $city, $street, $houseNo, $houseNoAddition, $nbcode, $lettercombination, $addresstype, $page)
    {
        return $this->_client->addressReeksFullParameterSearch(['province' => $province, 'district' => $district, 'city' => $city, 'street' => $street, 'houseNo' => $houseNo, 'houseNoAddition' => $houseNoAddition, 'nbcode' => $nbcode, 'lettercombination' => $lettercombination, 'addresstype' => $addresstype, 'page' => $page]);
    }

    /*
        Search for addresses in the <PCReeks> format, using different search
        phrases for each address part.
        
        Notice:
        <addressReeksFullParameterSearch> allows more parameters to search.
    
        http://webview.webservices.nl/documentation/files/service_address-class-php.html#Address.addressReeksParameterSearch
    
        Parameters:
            string $province - Phrase to search for in province name, or code of the province. See <PCReeks>.provincienaam and <PCReeks>.provinciecode
            string $district - Phrase used to select the municipality of the address, see <PCReeks>.gemeentenaam
            string $city - Phrase used to select the city of the address, see <PCReeks>.plaatsnaam
            string $street - Phrase used to select the street of the address, see <PCReeks>.straatnaam
            int $houseNo - Number used to select the house number of the address, see <PCReeks>.huisnr_van
            string $houseNoAddition - Phrase used to select the house number addition of the address
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <PCReeksSearchPartsPagedResult>.
    */
    public function addressReeksParameterSearch($province, $district, $city, $street, $houseNo, $houseNoAddition, $page)
    {
        return $this->_client->addressReeksParameterSearch(['province' => $province, 'district' => $district, 'city' => $city, 'street' => $street, 'houseNo' => $houseNo, 'houseNoAddition' => $houseNoAddition, 'page' => $page]);
    }

    /*
        Search for addresses in the <Perceel> format, using a single search
        phrase. The phrase can be a partial address. To search using
        separate fields for each addres part, use <addressPerceelFullParameterSearchV2>.
        
        PO box matches:
        PO box matches on PO box numbers are not returned by this method. Use <addressPerceelFullParameterSearchV2> if you also
        need to match PO box addresses. See <Perceel> for more information.
        
        Supported phrases:
        postcode, house number - 1188 VP, 202bis
        postcode - 1188 VP
        neighborhood code - 1188
        city, address - Amstelveen, Amsteldijk Zuid 202bis
        address, city - Amsteldijk Zuid 202bis, Amstelveen
        city, street - Amstelveen, Amsteldijk Zuid
        address - Amsteldijk Zuid 202bis
    
        http://webview.webservices.nl/documentation/files/service_address-class-php.html#Address.addressPerceelPhraseSearch
    
        Parameters:
            string $address - Address phrase to search for in addresses
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <PerceelSearchPartsPagedResult>.
    */
    public function addressPerceelPhraseSearch($address, $page)
    {
        return $this->_client->addressPerceelPhraseSearch(['address' => $address, 'page' => $page]);
    }

    /*
        Search for addresses in the <Perceel> format, using different search
        phrases for each address part. To search using a single combined phrase,
        use <addressPerceelPhraseSearch>.
        
        PO box matches:
        See <Perceel> for information on how PO box matches are returned.
    
        http://webview.webservices.nl/documentation/files/service_address-class-php.html#Address.addressPerceelFullParameterSearchV2
    
        Parameters:
            string $province - Phrase used to select the province of the address, see <Perceel>.provincienaam
            string $district - Phrase used to select the municipality of the address, see <Perceel>.gemeentenaam
            string $city - Phrase used to select the city of the address, see <Perceel>.plaatsnaam
            string $street - Phrase used to select the street of the address, see <Perceel>.straatnaam
            int $houseNo - Number used to select the house number of the address, see <Perceel>.huisnr
            string $houseNoAddition - Phrase used to select the house number addition of the address, see <Perceel>.huisnr_toevoeging
            string $nbcode - Number used to select the neighborhoodcode of the address, the first four numbers of the postcode. See <Perceel>.wijkcode
            string $lettercombination - Phrase used to select the lettercombination of the address, the last two letters of the postcode. See <Perceel>.lettercombinatie
            string $addresstype - Phrase used to select the addresstype of the address, see <Perceel>.reeksindicatie
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <PerceelSearchPartsPagedResult>.
    */
    public function addressPerceelFullParameterSearchV2($province, $district, $city, $street, $houseNo, $houseNoAddition, $nbcode, $lettercombination, $addresstype, $page)
    {
        return $this->_client->addressPerceelFullParameterSearchV2(['province' => $province, 'district' => $district, 'city' => $city, 'street' => $street, 'houseNo' => $houseNo, 'houseNoAddition' => $houseNoAddition, 'nbcode' => $nbcode, 'lettercombination' => $lettercombination, 'addresstype' => $addresstype, 'page' => $page]);
    }

    /*
        Returns a list of all neighborhood codes in the province
    
        http://webview.webservices.nl/documentation/files/service_address-class-php.html#Address.addressProvinceListNeighborhoods
    
        Parameters:
            string $name - Name or code of the province
            boolean $postbus - Boolean indicating whether Postbus neighborhood codes should be
                    included in the result
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <Neighborhood> entries.
    */
    public function addressProvinceListNeighborhoods($name, $postbus, $page)
    {
        return $this->_client->addressProvinceListNeighborhoods(['name' => $name, 'postbus' => $postbus, 'page' => $page]);
    }

    /*
        List all municipalities in a specific provinces.
    
        http://webview.webservices.nl/documentation/files/service_address-class-php.html#Address.addressProvinceListDistricts
    
        Parameters:
            string $name - Name or code of the province to list the municipalities from
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <District> entries.
    */
    public function addressProvinceListDistricts($name, $page)
    {
        return $this->_client->addressProvinceListDistricts(['name' => $name, 'page' => $page]);
    }

    /*
        List all provinces.
    
        http://webview.webservices.nl/documentation/files/service_address-class-php.html#Address.addressProvinceList
    
        Parameters:
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <Province> entries.
    */
    public function addressProvinceList($page)
    {
        return $this->_client->addressProvinceList(['page' => $page]);
    }

    /*
        Search for all provinces that match a phrase.
    
        http://webview.webservices.nl/documentation/files/service_address-class-php.html#Address.addressProvinceSearch
    
        Parameters:
            string $name - Phrase to search for in the province names, or the province code.
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <Province> entries.
    */
    public function addressProvinceSearch($name, $page)
    {
        return $this->_client->addressProvinceSearch(['name' => $name, 'page' => $page]);
    }

    /*
        Search for all municipalities that match a phrase.
    
        http://webview.webservices.nl/documentation/files/service_address-class-php.html#Address.addressDistrictSearch
    
        Parameters:
            string $name - Phrase to search municipalities for, or the numeric identifier for the municipality.
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <District> entries.
    */
    public function addressDistrictSearch($name, $page)
    {
        return $this->_client->addressDistrictSearch(['name' => $name, 'page' => $page]);
    }

    /*
        List all cities in specific municipalities
    
        http://webview.webservices.nl/documentation/files/service_address-class-php.html#Address.addressDistrictListCities
    
        Parameters:
            string $name - Phrase to search municipalities for, or the numeric identifier for the municipality.
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <City> entries.
    */
    public function addressDistrictListCities($name, $page)
    {
        return $this->_client->addressDistrictListCities(['name' => $name, 'page' => $page]);
    }

    /*
        Returns a list of all neighborhood codes in the municipality.
    
        http://webview.webservices.nl/documentation/files/service_address-class-php.html#Address.addressDistrictListNeighborhoods
    
        Parameters:
            string $name - Name or identifier of the municipality
            boolean $postbus - Boolean indicating whether Postbus neighborhood codes should be
                    included in the result
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <Neighborhood> entries.
    */
    public function addressDistrictListNeighborhoods($name, $postbus, $page)
    {
        return $this->_client->addressDistrictListNeighborhoods(['name' => $name, 'postbus' => $postbus, 'page' => $page]);
    }

    /*
        Search for all cities that match a phrase. Cities are also matched if input matches
        a commonly used alternative city name. Exact matches on the official name are listed first,
        the rest of the results are sorted alphabetically.
        
        This method differs from addressCitySearch by returning <CityV2> entries instead of <City> entries,
        thus giving more information about a city.
    
        http://webview.webservices.nl/documentation/files/service_address-class-php.html#Address.addressCitySearchV2
    
        Parameters:
            string $name - Phrase to search cities for, or the numeric identifier for the city.
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <CityV2> entries.
    */
    public function addressCitySearchV2($name, $page)
    {
        return $this->_client->addressCitySearchV2(['name' => $name, 'page' => $page]);
    }

    /*
        Returns a list of all neighborhood codes in the city
    
        http://webview.webservices.nl/documentation/files/service_address-class-php.html#Address.addressCityListNeighborhoods
    
        Parameters:
            string $name - Name or identifier of the city
            boolean $postbus - Boolean indicating whether Postbus neighborhood codes should be
                    included in the result
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <Neighborhood> entries.
    */
    public function addressCityListNeighborhoods($name, $postbus, $page)
    {
        return $this->_client->addressCityListNeighborhoods(['name' => $name, 'postbus' => $postbus, 'page' => $page]);
    }

    public function addressPerceelFullParameterSearch($province, $district, $city, $street, $houseNo, $houseNoAddition, $nbcode, $lettercombination, $addresstype, $page)
    {
        return $this->_client->addressPerceelFullParameterSearch(['province' => $province, 'district' => $district, 'city' => $city, 'street' => $street, 'houseNo' => $houseNo, 'houseNoAddition' => $houseNoAddition, 'nbcode' => $nbcode, 'lettercombination' => $lettercombination, 'addresstype' => $addresstype, 'page' => $page]);
    }

    public function addressPerceelParameterSearch($province, $district, $city, $street, $houseNo, $houseNoAddition, $page)
    {
        return $this->_client->addressPerceelParameterSearch(['province' => $province, 'district' => $district, 'city' => $city, 'street' => $street, 'houseNo' => $houseNo, 'houseNoAddition' => $houseNoAddition, 'page' => $page]);
    }

    public function addressNeighborhoodName($nbcode)
    {
        return $this->_client->addressNeighborhoodName(['nbcode' => $nbcode]);
    }

    public function addressNeighborhoodPhraseSearch($name, $page)
    {
        return $this->_client->addressNeighborhoodPhraseSearch(['name' => $name, 'page' => $page]);
    }

    /*
        Lookup the telephone areacodes related to a given neighborhoodcode.
    
        http://webview.webservices.nl/documentation/files/service_areacode-class-php.html#Areacode.areaCodeLookup
    
        Parameters:
            string $neighborhoodcode - neighborhoodcode to lookup
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <AreaCode> entries.
    */
    public function areaCodeLookup($neighborhoodcode, $page)
    {
        return $this->_client->areaCodeLookup(['neighborhoodcode' => $neighborhoodcode, 'page' => $page]);
    }

    /*
        Lookup the neighborhoodcodes related to a given telephone areacode.
    
        http://webview.webservices.nl/documentation/files/service_areacode-class-php.html#Areacode.areaCodeToNeighborhoodcode
    
        Parameters:
            string $areacode - Telephone areacode to lookup
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <Neighborhood> entries.
    */
    public function areaCodeToNeighborhoodcode($areacode, $page)
    {
        return $this->_client->areaCodeToNeighborhoodcode(['areacode' => $areacode, 'page' => $page]);
    }

    /*
        Lookup the telephone areacodes related to a given postcode.
    
        http://webview.webservices.nl/documentation/files/service_areacode-class-php.html#Areacode.areaCodePostcodeLookup
    
        Parameters:
            string $postcode - postcode to lookup
    

        Returns:
            out - A <Patterns::{Type}Array> of <AreaCode> entries.
    */
    public function areaCodePostcodeLookup($postcode)
    {
        return $this->_client->areaCodePostcodeLookup(['postcode' => $postcode]);
    }

    /*
        Attempt to authenticate using the username and password given.
    
        http://webview.webservices.nl/documentation/files/service_authentication-class-php.html#Authentication.login
    
        Parameters:
            string $username - Name of the user to authenticate
            string $password - Password of the user to authenticate
                    
                    See also:
                    <Using SOAP> for information on using the reactid to authenticate further requests
                    when using the SOAP interface.
    

        Returns:
            reactid  - A 32 character string that identifies a <User::Session>
    */
    public function login($username, $password)
    {
        return $this->_client->login(['username' => $username, 'password' => $password]);
    }

    /*
        End the session of the current user.
    
        http://webview.webservices.nl/documentation/files/service_authentication-class-php.html#Authentication.logout
    
        Parameters:
            None
    

        Returns:
            Nothing
    */
    public function logout()
    {
        return $this->_client->logout([]);
    }

    /*
        Retrieve a Bovag member using a Bovag identifier.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $bovag_id - The identifier used by Bovag to identify a member.
    

        Returns:
            out - <BovagMember>
    */
    public function bovagGetMemberByBovagId($bovag_id)
    {
        return $this->_client->bovagGetMemberByBovagId(['bovag_id' => $bovag_id]);
    }

    /*
        Retrieve a Bovag member using a DutchBusiness reference.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - The Chamber of Commerce number
            string $establishment_number - The Establishment number
    

        Returns:
            out - <BovagMember>
    */
    public function bovagGetMemberByDutchBusiness($dossier_number, $establishment_number)
    {
        return $this->_client->bovagGetMemberByDutchBusiness(['dossier_number' => $dossier_number, 'establishment_number' => $establishment_number]);
    }

    public function businessGetEstablishmentNumber($dossierno, $subdossierno)
    {
        return $this->_client->businessGetEstablishmentNumber(['dossierno' => $dossierno, 'subdossierno' => $subdossierno]);
    }

    public function businessGetBIKDescription($bikcode)
    {
        return $this->_client->businessGetBIKDescription(['bikcode' => $bikcode]);
    }

    public function businessGetSBIDescription($sbicode)
    {
        return $this->_client->businessGetSBIDescription(['sbicode' => $sbicode]);
    }

    public function businessBIKToSBI($bikcode)
    {
        return $this->_client->businessBIKToSBI(['bikcode' => $bikcode]);
    }

    public function businessSBIToBIK($sbicode)
    {
        return $this->_client->businessSBIToBIK(['sbicode' => $sbicode]);
    }

    public function businessGetDossierV3($dossierno, $subdossierno, $page)
    {
        return $this->_client->businessGetDossierV3(['dossierno' => $dossierno, 'subdossierno' => $subdossierno, 'page' => $page]);
    }

    public function businessGetDossierExtended($dossierno, $subdossierno)
    {
        return $this->_client->businessGetDossierExtended(['dossierno' => $dossierno, 'subdossierno' => $subdossierno]);
    }

    public function businessSearchDossierNumber($dossierno, $subdossierno, $page)
    {
        return $this->_client->businessSearchDossierNumber(['dossierno' => $dossierno, 'subdossierno' => $subdossierno, 'page' => $page]);
    }

    public function businessSearchPostcode($nbcode, $lettercomb, $houseno, $housenoaddition, $page)
    {
        return $this->_client->businessSearchPostcode(['nbcode' => $nbcode, 'lettercomb' => $lettercomb, 'houseno' => $houseno, 'housenoaddition' => $housenoaddition, 'page' => $page]);
    }

    public function businessSearchAddress($streetname, $houseno, $housenoaddition, $cityname, $page)
    {
        return $this->_client->businessSearchAddress(['streetname' => $streetname, 'houseno' => $houseno, 'housenoaddition' => $housenoaddition, 'cityname' => $cityname, 'page' => $page]);
    }

    public function businessSearchName($tradename, $page)
    {
        return $this->_client->businessSearchName(['tradename' => $tradename, 'page' => $page]);
    }

    public function businessSearchParameters($tradename, $cityname, $streetname, $nbcode, $lettercomb, $houseno, $housenoaddition, $telephoneno, $page)
    {
        return $this->_client->businessSearchParameters(['tradename' => $tradename, 'cityname' => $cityname, 'streetname' => $streetname, 'nbcode' => $nbcode, 'lettercomb' => $lettercomb, 'houseno' => $houseno, 'housenoaddition' => $housenoaddition, 'telephoneno' => $telephoneno, 'page' => $page]);
    }

    public function businessSearchParametersV3($tradename, $cityname, $streetname, $postcode, $houseno, $housenoaddition, $telephoneno, $page)
    {
        return $this->_client->businessSearchParametersV3(['tradename' => $tradename, 'cityname' => $cityname, 'streetname' => $streetname, 'postcode' => $postcode, 'houseno' => $houseno, 'housenoaddition' => $housenoaddition, 'telephoneno' => $telephoneno, 'page' => $page]);
    }

    public function businessSearchSelection($city, $postcode, $sbi, $primary_sbi_only, $legal_forms, $employees_min, $employees_max, $economically_active, $financial_status, $changed_since, $page)
    {
        return $this->_client->businessSearchSelection(['city' => $city, 'postcode' => $postcode, 'sbi' => $sbi, 'primary_sbi_only' => $primary_sbi_only, 'legal_forms' => $legal_forms, 'employees_min' => $employees_min, 'employees_max' => $employees_max, 'economically_active' => $economically_active, 'financial_status' => $financial_status, 'changed_since' => $changed_since, 'page' => $page]);
    }

    public function businessSearchSelectionV2($city, $postcode, $sbi, $primary_sbi_only, $legal_forms, $employees_min, $employees_max, $economically_active, $financial_status, $changed_since, $new_since, $page)
    {
        return $this->_client->businessSearchSelectionV2(['city' => $city, 'postcode' => $postcode, 'sbi' => $sbi, 'primary_sbi_only' => $primary_sbi_only, 'legal_forms' => $legal_forms, 'employees_min' => $employees_min, 'employees_max' => $employees_max, 'economically_active' => $economically_active, 'financial_status' => $financial_status, 'changed_since' => $changed_since, 'new_since' => $new_since, 'page' => $page]);
    }

    public function businessGetDossierSBI($dossierno, $subdossierno)
    {
        return $this->_client->businessGetDossierSBI(['dossierno' => $dossierno, 'subdossierno' => $subdossierno]);
    }

    public function businessUpdateCheckDossier($dossierno, $subdossierno, $update_types)
    {
        return $this->_client->businessUpdateCheckDossier(['dossierno' => $dossierno, 'subdossierno' => $subdossierno, 'update_types' => $update_types]);
    }

    public function businessUpdateGetChangedDossiers($changed_since, $update_types, $page)
    {
        return $this->_client->businessUpdateGetChangedDossiers(['changed_since' => $changed_since, 'update_types' => $update_types, 'page' => $page]);
    }

    public function businessUpdateGetDossiers($update_types, $page)
    {
        return $this->_client->businessUpdateGetDossiers(['update_types' => $update_types, 'page' => $page]);
    }

    public function businessUpdateAddDossier($dossierno, $subdossierno)
    {
        return $this->_client->businessUpdateAddDossier(['dossierno' => $dossierno, 'subdossierno' => $subdossierno]);
    }

    public function businessUpdateRemoveDossier($dossierno, $subdossierno)
    {
        return $this->_client->businessUpdateRemoveDossier(['dossierno' => $dossierno, 'subdossierno' => $subdossierno]);
    }

    public function businessSearchParametersV2($tradename, $cityname, $streetname, $postcode, $houseno, $housenoaddition, $telephoneno, $page)
    {
        return $this->_client->businessSearchParametersV2(['tradename' => $tradename, 'cityname' => $cityname, 'streetname' => $streetname, 'postcode' => $postcode, 'houseno' => $houseno, 'housenoaddition' => $housenoaddition, 'telephoneno' => $telephoneno, 'page' => $page]);
    }

    /*
        Check the validity of a license plate and check code ('meldcode') combination.
        
        This method differs from <carVWEMeldcodeCheck> in that it also returns whether a
        car is active.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carRDWCarCheckCode
    
        Parameters:
            string $license_plate - Dutch license plate (kenteken)
            string $code - code (meldcode), 4 digits
    

        Returns:
            out - A <CarCheckCode>.
    */
    public function carRDWCarCheckCode($license_plate, $code)
    {
        return $this->_client->carRDWCarCheckCode(['license_plate' => $license_plate, 'code' => $code]);
    }

    /*
        Retrieves data of a car with a Dutch license plate, including a list of types
        matched if more information is available.
        
        This method differs from <carRDWCarDataV2> in that it also returns the CO2 emission.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carRDWCarDataV3
    
        Parameters:
            string $license_plate - Dutch license plate (kenteken) of the car to retrieve
    

        Returns:
            out - A <CarDataV3Result>.
    */
    public function carRDWCarDataV3($license_plate)
    {
        return $this->_client->carRDWCarDataV3(['license_plate' => $license_plate]);
    }

    /*
        Retrieves data of a car with a Dutch license plate.
        In addition to the information returned by <carRDWCarData>
        data on BPM and power is returned.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carRDWCarDataBPV2
    
        Parameters:
            string $license_plate - Dutch license plate (kenteken) of the car to retreive
    

        Returns:
            out - A <CarBPV2>.
    */
    public function carRDWCarDataBPV2($license_plate)
    {
        return $this->_client->carRDWCarDataBPV2(['license_plate' => $license_plate]);
    }

    /*
        Retrieves data of a car with a Dutch license plate and check code ('meldcode').
        The car data contains the European Approval Mark according to the 2007/46/EG standard.
        
        When the code is set it also checks the validity of a license plate and check code ('meldcode') combination.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carRDWCarDataExtended
    
        Parameters:
            string $license_plate - Dutch license plate (kenteken) of the car to retreive
            string $code - code (meldcode), 4 digits (optional)
    

        Returns:
            out - A <CarExtended>.
    */
    public function carRDWCarDataExtended($license_plate, $code)
    {
        return $this->_client->carRDWCarDataExtended(['license_plate' => $license_plate, 'code' => $code]);
    }

    /*
        Retrieves car data, including the fiscal price, directly from RDW. The fiscal price
        is the catalogue price of the vehicle, used by the tax department to calculate the
        tax for the private use of a leased car.
        
        Retrieves data of a car with a Dutch license plate.
        This method returns data on the fiscal price, power, environmental impact, status
        and all information returned by <carRDWCarDataV3>.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carRDWCarDataPrice
    
        Parameters:
            string $license_plate - Dutch license plate (kenteken)
    

        Returns:
            out - A <CarRDWCarDataPrice>
    */
    public function carRDWCarDataPrice($license_plate)
    {
        return $this->_client->carRDWCarDataPrice(['license_plate' => $license_plate]);
    }

    /*
        Retrieves data of a car, including information about extra options.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carRDWCarDataOptions
    
        Parameters:
            string $car_id - Unique identifier for a car and type. Use <carRDWCarDataV3> to find a car_id
    

        Returns:
            out - A <CarOptions>.
    */
    public function carRDWCarDataOptions($car_id)
    {
        return $this->_client->carRDWCarDataOptions(['car_id' => $car_id]);
    }

    /*
        Check the validity of a license plate and check code ('meldcode') combination.
        
        Please note that when using a test account an older and less complete dataset is used.
        
        See <carRDWCarCheckCode> if you want to check whether a car is active.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carVWEMeldcodeCheck
    
        Parameters:
            string $license_plate - Dutch license plate (kenteken)
            string $code - code (meldcode), 4 digits
    

        Returns:
            out - A <CarVWEMeldcodeCheck>.
    */
    public function carVWEMeldcodeCheck($license_plate, $code)
    {
        return $this->_client->carVWEMeldcodeCheck(['license_plate' => $license_plate, 'code' => $code]);
    }

    /*
        Retrieve extended information for a car. This function returns more information
        than <carRDWCarData> or <carRDWCarDataBP>.
        
        Please note that when using a test account an older and less complete dataset is used.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carVWEBasicTypeData
    
        Parameters:
            string $license_plate - Dutch license plate (kenteken)
    

        Returns:
            out - A <CarVWEBasicTypeData>
    */
    public function carVWEBasicTypeData($license_plate)
    {
        return $this->_client->carVWEBasicTypeData(['license_plate' => $license_plate]);
    }

    /*
        Retrieve extended information for a specific version of a car.
        
        Please note that when using a test account an older and less complete dataset is used.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carVWEVersionPrice
    
        Parameters:
            string $license_plate - Dutch license plate (kenteken)
            int $atl_code - Code identifying the version of the car. The ATL code can be obtained using <carVWEBasicTypeData>.
    

        Returns:
            out - A <CarVWEVersionPrice>
    */
    public function carVWEVersionPrice($license_plate, $atl_code)
    {
        return $this->_client->carVWEVersionPrice(['license_plate' => $license_plate, 'atl_code' => $atl_code]);
    }

    /*
        Retrieve options of a car.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carVWEOptions
    
        Parameters:
            string $license_plate - The license plate of a car
            int $atl_code - Code identifying the version of the car. The ATL code can be obtained using <carVWEBasicTypeData>.
    

        Returns:
            out - <CarVWEOptions>
    */
    public function carVWEOptions($license_plate, $atl_code)
    {
        return $this->_client->carVWEOptions(['license_plate' => $license_plate, 'atl_code' => $atl_code]);
    }

    /*
        Please note that when using a test account an older and less complete dataset is used.
        
        Retrieve possible brands for a specific kind of car.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carVWEListBrands
    
        Parameters:
            int $production_year - Search for brands which produced cars in this year, or one year before or after. If 0, brands of all years are returned.
            int $kind_id - Identifier of the kind of car to retrieve the brands for.
                    1 -- passenger car, yellow license plate
                    2 -- delivery trucks, company cars, up to 3.5 tons
                    3 -- delivery trucks, company cars, up to 10 tons
                    4 -- off-road four wheel drives
                    5 -- motorcycles
                    6 -- moped
                    8 -- bus
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <CarVWEBrand> entries
    */
    public function carVWEListBrands($production_year, $kind_id, $page)
    {
        return $this->_client->carVWEListBrands(['production_year' => $production_year, 'kind_id' => $kind_id, 'page' => $page]);
    }

    /*
        Retrieve possible models for a specific brand of car.
        
        Please note that when using a test account an older and less complete dataset is used.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carVWEListModels
    
        Parameters:
            int $production_year - Search for models which were produced in this year, or one year before or after. If 0, models of all years are returned.
            int $kind_id - Identifier of the kind of car to retrieve the models for.
                    1 -- passenger car, yellow license plate
                    2 -- delivery trucks, company cars, up to 3.5 tons
                    3 -- delivery trucks, company cars, up to 10 tons
                    4 -- off-road four wheel drives
                    5 -- motorcycles
                    6 -- moped
                    8 -- bus
            int $brand_id - Brand identifier, as returned by <carVWEListBrands>.
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <CarVWEModel> entries
    */
    public function carVWEListModels($production_year, $kind_id, $brand_id, $page)
    {
        return $this->_client->carVWEListModels(['production_year' => $production_year, 'kind_id' => $kind_id, 'brand_id' => $brand_id, 'page' => $page]);
    }

    /*
        Retrieve possible versions for a specific model of car.
        
        Please note that when using a test account an older and less complete dataset is used.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carVWEListVersions
    
        Parameters:
            int $production_year - Search for versions which were produced in this year, or one year before or after. If 0, versions of all years are returned.
            int $kind_id - Identifier of the kind of car to retrieve the versions for.
                    1 -- passenger car, yellow license plate
                    2 -- delivery trucks, company cars, up to 3.5 tons
                    3 -- delivery trucks, company cars, up to 10 tons
                    4 -- off-road four wheel drives
                    5 -- motorcycles
                    6 -- moped
                    8 -- bus
            int $brand_id - Brand identifier, as returned by <carVWEListBrands>.
            int $model_id - Model identifier, as returned by <carVWEListModels>.
            int $fuel_type_id - Fuel type identifier. Optional.
                    1 -- Benzine
                    2 -- Diesel
                    3 -- Electrisch
                    4 -- Hybride Benzine
                    5 -- Hybride Diesel
            int $body_style_id - Body style identifier. Optional.
                    2 -- 2/4-drs sedan (2/4 deurs sedan)
                    3 -- 3/5-drs (3/5 deurs hatchback)
                    4 -- Coupé
                    5 -- 2-drs (2 deurs cabrio)
                    6 -- Hardtop
                    7 -- 3/5-drs (3/5 deurs softtop)
                    8 -- 2-drs (2 deurs targa)
                    9 -- 5-drs (5 deurs liftback)
                    10 -- 3/4/5-drs (combi 3/4/5 deurs)
                    14 -- afg. pers. auto (afgeleid van personenauto, voertuig met grijs kenteken)
                    15 -- bedrijfsauto (bestel/bedrijfsauto)
                    16 -- pers. vervoer (bus, personenvervoer)
                    17 -- open laadbak (pick-up truck)
                    18 -- Chassis+Cabine
                    19 -- Kaal Chassis
                    20 -- MPV=ruimtewagen
                    21 -- SportUtilityVeh (SUV)
            int $doors - Number of doors. If the design is 2/4-drs or 3/5-drs, this parameter can distinguish between
                    the two models. Typical values: 2, 3, 4, 5. Optional.
            int $gear_id - Type of gearbox. Optional.
                    1 -- Handgeschakeld
                    2 -- Automatisch
                    3 -- Handgeschakeld, 4 versnellingen
                    4 -- Handgeschakeld, 5 versnellingen
                    5 -- Handgeschakeld, 6 versnellingen
                    6 -- Handgeschakeld, 7 versnellingen
                    13 -- Semi-automatisch
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <CarVWEVersion> entries
    */
    public function carVWEListVersions($production_year, $kind_id, $brand_id, $model_id, $fuel_type_id, $body_style_id, $doors, $gear_id, $page)
    {
        return $this->_client->carVWEListVersions(['production_year' => $production_year, 'kind_id' => $kind_id, 'brand_id' => $brand_id, 'model_id' => $model_id, 'fuel_type_id' => $fuel_type_id, 'body_style_id' => $body_style_id, 'doors' => $doors, 'gear_id' => $gear_id, 'page' => $page]);
    }

    /*
        Retrieve extended information for a specific version of a car.
        
        Please note that when using a test account an older and less complete dataset is used.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carVWEVersionYearData
    
        Parameters:
            int $production_year - Get information for the model produced in this year. This affects the <CarVWEPrices> in the result.
            int $atl_code - Code identifying the version of the car. This atl_code can be obtained using <carVWEListBrands>,
                    <carVWEListModels> and <carVWEListVersions> consecutively, or by using <carVWEBasicTypeData> when it concerns a
                    specific car (requires license plate).
    

        Returns:
            out - A <CarVWEVersionYearData>
    */
    public function carVWEVersionYearData($production_year, $atl_code)
    {
        return $this->_client->carVWEVersionYearData(['production_year' => $production_year, 'atl_code' => $atl_code]);
    }

    /*
        Retrieve photos of a car using it's unique atl_code.
        
        Please note that when using a test account an older and less complete dataset is used.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carVWEPhotos
    
        Parameters:
            int $atl_code - Code identifying the version of the car. Code identifying the version of the car. This atl_code can be obtained using <carVWEListBrands>,
                    <carVWEListModels> and <carVWEListVersions> consecutively or by using <carVWEBasicTypeData> when it concerns a
                    specific car (requires license plate)
    

        Returns:
            out - A <Patterns::{Type}Array> of <CarVWEPhoto> entries
    */
    public function carVWEPhotos($atl_code)
    {
        return $this->_client->carVWEPhotos(['atl_code' => $atl_code]);
    }

    /*
        Retrieve Autodisk price information. Autodisk data is available for yellow license plates no older than 2004.
        Coverage for older plates is very limited.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carATDPrice
    
        Parameters:
            string $license_plate - Dutch license plate (kenteken)
    

        Returns:
            out - A <CarATDPrices>
    */
    public function carATDPrice($license_plate)
    {
        return $this->_client->carATDPrice(['license_plate' => $license_plate]);
    }

    /*
        Retrieves data of a car with a Dutch license plate
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carRDWCarData
    
        Parameters:
            string $license_plate - Dutch license plate (kenteken) of the car to retrieve
    

        Returns:
            out - A <Car>.
    */
    public function carRDWCarData($license_plate)
    {
        return $this->_client->carRDWCarData(['license_plate' => $license_plate]);
    }

    /*
        Retrieves data of a car with a Dutch license plate.
        In addition to the information returned by <carRDWCarData>
        data on BPM and power is returned.
    
        http://webview.webservices.nl/documentation/files/service_car-class-php.html#Car.carRDWCarDataBP
    
        Parameters:
            string $license_plate - Dutch license plate (kenteken) of the car to retreive
    

        Returns:
            out - A <CarBP>.
    */
    public function carRDWCarDataBP($license_plate)
    {
        return $this->_client->carRDWCarDataBP(['license_plate' => $license_plate]);
    }

    /*
        Retrieve a detailed company report.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $company_id - Company ID, as returned by <creditsafeSearch>. Due to legal reasons
                    all report requests of German companies (DE) must be accompanied with a reason code.
                    To specify a report request reason code, append one of the following codes onto the company_id (without quotes):
                    '|1' -- Credit inquiry
                    '|2' -- Business Relationship
                    '|3' -- Solvency Check
                    '|4' -- Claim
                    '|5' -- Contract
                    '|6' -- Commercial Credit Insurance
            string $language - ISO 639-1 notation language that the report should be returned in, for example: "EN".
                    Available languages for a company are returned by <creditsafeSearch>.
            string $document - Specify to retrieve an extra document with an excerpt of the data. Currently unused. Possible values:
                    [empty string] -- Return no extra document.
    

        Returns:
            out - A <CreditsafeCompanyReportFull> entry.
    */
    public function creditsafeGetReportFull($company_id, $language, $document)
    {
        return $this->_client->creditsafeGetReportFull(['company_id' => $company_id, 'language' => $language, 'document' => $document]);
    }

    /*
        Search for a company.
        
        The parameters which can be used differ for each country. The parameters that can be used are described in <Country parameters>.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $country - The country to search in, An ISO 3166-1 alpha-2 country code, optional
            string $id - Search a single company, using the Creditsafe company identifier, optional
            string $registration_number - Search using a company registration number, optional
            string $status - Search using a company status. See <Country parameters> for allowed values per country, optional
            string $office_type - Search using a company office type. See <Country parameters> for allowed values per country, optional
            string $name - Search using a company name, optional
            string $name_match_type - How to match the text in the *name* parameter, the default match and possibles types are given in <Country parameters> for each country, optional
            string $address - Search using a company's complete address, optional
            string $address_match_type - How to match the text in the *address* parameter, the default match type and possibles types are given in <Country parameters> for each country, optional
            string $street - Search using a company's address street, optional
            string $house_number - Search using a company's address house number, optional
            string $city - Search using a company's address city, optional
            string $postal_code - Search using a company's address postal code, optional
            string $province - Search using a company's address province, optional
            string $phone_number - Search using a company's phone number, optional
            int $page - Page of search results to retrieve
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <CreditsafeCompany> entries.
    */
    public function creditsafeSearch($country, $id, $registration_number, $status, $office_type, $name, $name_match_type, $address, $address_match_type, $street, $house_number, $city, $postal_code, $province, $phone_number, $page)
    {
        return $this->_client->creditsafeSearch(['country' => $country, 'id' => $id, 'registration_number' => $registration_number, 'status' => $status, 'office_type' => $office_type, 'name' => $name, 'name_match_type' => $name_match_type, 'address' => $address, 'address_match_type' => $address_match_type, 'street' => $street, 'house_number' => $house_number, 'city' => $city, 'postal_code' => $postal_code, 'province' => $province, 'phone_number' => $phone_number, 'page' => $page]);
    }

    /*
        Search for a business on name and location. This method returns basic information and a DNB business key for each business.
        
        Business can be searched on name, with optional address parameters. Searching on address can be done using the postcode, or the city and at least one
        other address field.
        
        See <Dun & Bradstreet::Company identifiers>
    
        http://webview.webservices.nl/documentation/files/service_dnb-class-php.html#DunBradstreet.dnbSearchReferenceV2
    
        Parameters:
            string $name - Trade name of the business, required.
            string $streetname - Street the business is located at, optional.
            string $houseno - Housenumber of the business, optional.
            string $housenoaddition - Housenumber addition, optional.
            string $postcode - Postcode of the business, optional.
            string $cityname - City where the business is located, optional.
            string $region - Depending on the country, this may be a state, province, or other large geographical area.
                    For searches in the United States (US) and Canada (CA) this parameter is required. State abbreviations,
                    such as NY for New York, must be used for the US.
            string $country - The 2 character ISO 3166-1 code for the country where the business is located, e.g. "nl". Required.
            int $page - Page to retrieve, pages start counting at 1.
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <DNBBusinessReferenceV2> entries.
    */
    public function dnbSearchReferenceV2($name, $streetname, $houseno, $housenoaddition, $postcode, $cityname, $region, $country, $page)
    {
        return $this->_client->dnbSearchReferenceV2(['name' => $name, 'streetname' => $streetname, 'houseno' => $houseno, 'housenoaddition' => $housenoaddition, 'postcode' => $postcode, 'cityname' => $cityname, 'region' => $region, 'country' => $country, 'page' => $page]);
    }

    /*
        Retrieve a <DNBBusinessReference> for a business.
    
        http://webview.webservices.nl/documentation/files/service_dnb-class-php.html#DunBradstreet.dnbGetReference
    
        Parameters:
            string $company_id - Identifier for the business. The field company_id_type indicates the type of this field.
            string $company_id_type - Type of company identifier, see <Dun & Bradstreet::Company identifiers>. Possible values:
                    duns     -- DUNS number
                    dnb_key  -- D&B business key
                    nl|us|.. -- A 2 character ISO 3166-1 country code. Use this if the company_id is a regional business number.
                    For the Netherlands (NL) it can either be an 8-digit Chamber of Commerce Number (KvK-nummer),
                    a 12-digit Establishment Number (Vestigingsnummer), or a 9-digit RSIN Number
                    (Rechtspersonen Samenwerkingsverbanden Informatie Nummer).
    

        Returns:
            out - a <DNBBusinessReference> with name, address and D&B business key. The field confidence_code in the <DNBBusinessReference>
            is always 10, because this function only returns a result when an exact match was found.
    */
    public function dnbGetReference($company_id, $company_id_type)
    {
        return $this->_client->dnbGetReference(['company_id' => $company_id, 'company_id_type' => $company_id_type]);
    }

    /*
        Retrieve basic WorldBase business information.
    
        http://webview.webservices.nl/documentation/files/service_dnb-class-php.html#DunBradstreet.dnbWorldbaseMarketing
    
        Parameters:
            string $company_id - Identifier for the business. The field company_id_type indicates the type of this field.
            string $company_id_type - Type of company identifier, see <Dun & Bradstreet::Company identifiers>. Possible values:
                    duns     -- DUNS number
                    dnb_key  -- D&B business key
                    nl|us|.. -- A 2 character ISO 3166-1 country code. Use this if the company_id is a regional business number.
                    For the Netherlands (NL) it can either be an 8-digit Chamber of Commerce Number (KvK-nummer),
                    a 12-digit Establishment Number (Vestigingsnummer), or a 9-digit RSIN Number
                    (Rechtspersonen Samenwerkingsverbanden Informatie Nummer).
    

        Returns:
            out - <DNBMarketing> data
    */
    public function dnbWorldbaseMarketing($company_id, $company_id_type)
    {
        return $this->_client->dnbWorldbaseMarketing(['company_id' => $company_id, 'company_id_type' => $company_id_type]);
    }

    /*
        Retrieve detailed WorldBase business information.
    
        http://webview.webservices.nl/documentation/files/service_dnb-class-php.html#DunBradstreet.dnbWorldbaseMarketingPlus
    
        Parameters:
            string $company_id - Identifier for the business. The field company_id_type indicates the type of this field.
            string $company_id_type - Type of company identifier, see <Dun & Bradstreet::Company identifiers>. Possible values:
                    duns     -- DUNS number
                    dnb_key  -- D&B business key
                    nl|us|.. -- A 2 character ISO 3166-1 country code. Use this if the company_id is a regional business number.
                    For the Netherlands (NL) it can either be an 8-digit Chamber of Commerce Number (KvK-nummer),
                    a 12-digit Establishment Number (Vestigingsnummer), or a 9-digit RSIN Number
                    (Rechtspersonen Samenwerkingsverbanden Informatie Nummer).
    

        Returns:
            out - <DNBMarketingPlusResult> data
    */
    public function dnbWorldbaseMarketingPlus($company_id, $company_id_type)
    {
        return $this->_client->dnbWorldbaseMarketingPlus(['company_id' => $company_id, 'company_id_type' => $company_id_type]);
    }

    /*
        Detailed WorldBase information, including information on a business' family tree.
    
        http://webview.webservices.nl/documentation/files/service_dnb-class-php.html#DunBradstreet.dnbWorldbaseMarketingPlusLinkage
    
        Parameters:
            string $company_id - Identifier for the business. The field company_id_type indicates the type of this field.
            string $company_id_type - Type of company identifier, see <Dun & Bradstreet::Company identifiers>. Possible values:
                    duns     -- DUNS number
                    dnb_key  -- D&B business key
                    nl|us|.. -- A 2 character ISO 3166-1 country code. Use this if the company_id is a regional business number.
                    For the Netherlands (NL) it can either be an 8-digit Chamber of Commerce Number (KvK-nummer),
                    a 12-digit Establishment Number (Vestigingsnummer), or a 9-digit RSIN Number
                    (Rechtspersonen Samenwerkingsverbanden Informatie Nummer).
    

        Returns:
            out - <DNBMarketingPlusLinkageResult> data
    */
    public function dnbWorldbaseMarketingPlusLinkage($company_id, $company_id_type)
    {
        return $this->_client->dnbWorldbaseMarketingPlusLinkage(['company_id' => $company_id, 'company_id_type' => $company_id_type]);
    }

    /*
        Do a D&B Quick Check on a business.
    
        http://webview.webservices.nl/documentation/files/service_dnb-class-php.html#DunBradstreet.dnbQuickCheck
    
        Parameters:
            string $company_id - Identifier for the business. The field company_id_type indicates the type of this field.
            string $company_id_type - Type of company identifier, see <Dun & Bradstreet::Company identifiers>. Possible values:
                    duns     -- DUNS number
                    dnb_key  -- D&B business key
                    nl|us|.. -- A 2 character ISO 3166-1 country code. Use this if the company_id is a regional business number.
                    For the Netherlands (NL) it can either be an 8-digit Chamber of Commerce Number (KvK-nummer),
                    a 12-digit Establishment Number (Vestigingsnummer), or a 9-digit RSIN Number
                    (Rechtspersonen Samenwerkingsverbanden Informatie Nummer).
    

        Returns:
            out - a <DNBQuickCheck> with information on location, situation, size and financial status on the business.
    */
    public function dnbQuickCheck($company_id, $company_id_type)
    {
        return $this->_client->dnbQuickCheck(['company_id' => $company_id, 'company_id_type' => $company_id_type]);
    }

    /*
        Perform a D&B Business Verification on a business.
    
        http://webview.webservices.nl/documentation/files/service_dnb-class-php.html#DunBradstreet.dnbBusinessVerification
    
        Parameters:
            string $company_id - Identifier for the business. The field company_id_type indicates the type of this field.
            string $company_id_type - Type of company identifier, see <Dun & Bradstreet::Company identifiers>. Possible values:
                    duns     -- DUNS number
                    dnb_key  -- D&B business key
                    nl|us|.. -- A 2 character ISO 3166-1 country code. Use this if the company_id is a regional business number.
                    For the Netherlands (NL) it can either be an 8-digit Chamber of Commerce Number (KvK-nummer),
                    a 12-digit Establishment Number (Vestigingsnummer), or a 9-digit RSIN Number
                    (Rechtspersonen Samenwerkingsverbanden Informatie Nummer).
    

        Returns:
            out - a <DNBBusinessVerification> with information on location, situation, size and financial status on the business.
    */
    public function dnbBusinessVerification($company_id, $company_id_type)
    {
        return $this->_client->dnbBusinessVerification(['company_id' => $company_id, 'company_id_type' => $company_id_type]);
    }

    /*
        Retrieve extensive management information on a business.
    
        http://webview.webservices.nl/documentation/files/service_dnb-class-php.html#DunBradstreet.dnbEnterpriseManagement
    
        Parameters:
            string $company_id - Identifier for the business. The field company_id_type indicates the type of this field.
            string $company_id_type - Type of company identifier, see <Dun & Bradstreet::Company identifiers>. Possible values:
                    duns     -- DUNS number
                    dnb_key  -- D&B business key
                    nl|us|.. -- A 2 character ISO 3166-1 country code. Use this if the company_id is a regional business number.
                    For the Netherlands (NL) it can either be an 8-digit Chamber of Commerce Number (KvK-nummer),
                    a 12-digit Establishment Number (Vestigingsnummer), or a 9-digit RSIN Number
                    (Rechtspersonen Samenwerkingsverbanden Informatie Nummer).
    

        Returns:
            out - a <DNBEnterpriseManagement> with all information in the <DNBQuickCheck> and information on financial status and credit scores.
    */
    public function dnbEnterpriseManagement($company_id, $company_id_type)
    {
        return $this->_client->dnbEnterpriseManagement(['company_id' => $company_id, 'company_id_type' => $company_id_type]);
    }

    /*
        Notice:
        This method is deprecated, use <dnbSearchReferenceV2> instead.
        
        Search for a business on name and location. This method returns basic information and a DNB business key for each business.
        
        Business can be searched on name, with optional address parameters. Searching on address can be done using the postcode, or the city and at least one
        other address field.
        
        See <Dun & Bradstreet::Company identifiers>
    
        http://webview.webservices.nl/documentation/files/service_dnb-class-php.html#DunBradstreet.dnbSearchReference
    
        Parameters:
            string $name - Trade name of the business, required.
            string $streetname - Street the business is located at, optional.
            string $houseno - Housenumber of the business, optional.
            string $housenoaddition - Housenumber addition, optional.
            string $postcode - Postcode of the business, optional.
            string $cityname - City where the business is located, optional.
            string $country - The 2 character ISO 3166-1 code for the country where the business is located, e.g. "nl". Required.
            int $page - Page to retrieve, pages start counting at 1.
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <DNBBusinessReference> entries.
    */
    public function dnbSearchReference($name, $streetname, $houseno, $housenoaddition, $postcode, $cityname, $country, $page)
    {
        return $this->_client->dnbSearchReference(['name' => $name, 'streetname' => $streetname, 'houseno' => $houseno, 'housenoaddition' => $housenoaddition, 'postcode' => $postcode, 'cityname' => $cityname, 'country' => $country, 'page' => $page]);
    }

    /*
        Lookup the driving distance in meters between two neighborhoodcodes
        for both the fastest and shortest route.
    
        http://webview.webservices.nl/documentation/files/service_driveinfo-class-php.html#Driveinfo.driveInfoDistanceLookup
    
        Parameters:
            string $nbcodefrom - neighborhoodcode at start of route
            string $nbcodeto - destination neighborhoodcode
    

        Returns:
            out - A <DriveInfo> entry.
    */
    public function driveInfoDistanceLookup($nbcodefrom, $nbcodeto)
    {
        return $this->_client->driveInfoDistanceLookup(['nbcodefrom' => $nbcodefrom, 'nbcodeto' => $nbcodeto]);
    }

    /*
        Lookup the driving time in minutes between two neighborhoodcodes
        for both the fastest and shortest route.
    
        http://webview.webservices.nl/documentation/files/service_driveinfo-class-php.html#Driveinfo.driveInfoTimeLookup
    
        Parameters:
            string $nbcodefrom - neighborhoodcode at start of route
            string $nbcodeto - destination neighborhoodcode
    

        Returns:
            out - A <DriveInfo> entry.
    */
    public function driveInfoTimeLookup($nbcodefrom, $nbcodeto)
    {
        return $this->_client->driveInfoTimeLookup(['nbcodefrom' => $nbcodefrom, 'nbcodeto' => $nbcodeto]);
    }

    /*
        Determine if a specific address exists using the unique '1234AA12'
        postcode + house number format. If returns either the full address in
        <DutchAddressPostcodeRange> format, or an error if no matching address exists.
        
        Migrating from <Address::addressReeksPostcodeSearch>:
        
        This method provides the same basic functionality as <Address::addressReeksPostcodeSearch>.
        If you currently use addressReeksPostcodeSearch and want to migrate to dutchAddressRangePostcodeSearch,
        there are several differences you need to consider.
        
        First of all, the output structure is different. A <DutchAddressPostcodeRange> is returned instead of a
        <Address::PCReeks>. All output elements have been renamed, and some rarely used elements have been removed.
        The differences are documented in the documentation of <DutchAddressPostcodeRange>.
        
        Secondly, all error handling should be done based on only <Error Handling::Error codes>. Error specific strings such
        as 'postcode::address_get_by_postcode::unknown_address' are no longer returned.
        
        Finally, house boat and trailer addresses are handled differently. House boat or trailer flags (AB/WW) in combination
        with house number 0 are no longer supported. House boats and trailer addresses can be validated using their postcode
        and house number combination like any other address. Separate range indications for house boats and trailers
        are no longer used. The empty range indication for 'vacant' addresses is also no longer applicable.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $address - Address to validate, in the unique '1234AA12' postcode house number format.
    

        Returns:
            out - A <DutchAddressPostcodeRange> structure.
    */
    public function dutchAddressRangePostcodeSearch($address)
    {
        return $this->_client->dutchAddressRangePostcodeSearch(['address' => $address]);
    }

    /*
        Retrieve data on a business establishment. When only the dossier number parameter is specified,
        the main establishment of the business will be returned. Specify the establishment_number in order
        to retrieve another establishment.
        
        You can find dossier and establishment numbers using <dutchBusinessSearchParameters> or <dutchBusinessSearchDossierNumber>.
        
        Update service:
        
        If logging of data is enabled for the user, the requested dossier is logged. This enables the user to
        receive updates to the dossier in the future. See <Dutch Business update service methods>.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - The Chamber of Commerce number
            string $establishment_number - The Establishment number
    

        Returns:
            out - A <DutchBusinessDossier>
    */
    public function dutchBusinessGetDossier($dossier_number, $establishment_number)
    {
        return $this->_client->dutchBusinessGetDossier(['dossier_number' => $dossier_number, 'establishment_number' => $establishment_number]);
    }

    /*
        
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - The Chamber of Commerce number
    

        Returns:
            out - A <DutchBusinessVatNumber>
    */
    public function dutchBusinessGetVatNumber($dossier_number)
    {
        return $this->_client->dutchBusinessGetVatNumber(['dossier_number' => $dossier_number]);
    }

    /*
        Get the business positions/functionaries for a business
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - The Chamber of Commerce number
    

        Returns:
            out - A <DutchBusinessPositions> entry
    */
    public function dutchBusinessGetPositions($dossier_number)
    {
        return $this->_client->dutchBusinessGetPositions(['dossier_number' => $dossier_number]);
    }

    /*
        Search for business establishments using a known identifier. Any combination of parameters may be specified.
        Only businesses matching all parameters will be returned.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - The Chamber of Commerce number
            string $establishment_number - The Establishment number
            string $rsin_number - The RSIN (`Rechtspersonen Samenwerkingsverbanden Informatie Nummer`) number
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <DutchBusinessReference>
    */
    public function dutchBusinessSearchDossierNumber($dossier_number, $establishment_number, $rsin_number, $page)
    {
        return $this->_client->dutchBusinessSearchDossierNumber(['dossier_number' => $dossier_number, 'establishment_number' => $establishment_number, 'rsin_number' => $rsin_number, 'page' => $page]);
    }

    /*
        Find business establishments using a variety of parameters. Found dossiers are ordered by relevance,
        ensuring the dossiers that match the search parameters best are listed at the top of the result list.
        This method differs from <dutchBusinessSearchParameters> by returning an indication called "match_type" that
        defines what type of business name was matched upon (see <Tradename match types>).
        
        Using the search parameters:
        - *trade_name* parameter will be used to search all business names for the dossiers, which include the trade name, legal name and alternative trade names.
        - *address* parameters are matched against both the correspondence and establishment addresses of the business.
        - *postbus* addresses can be found by specifying 'Postbus' as street, and specifying the postbus number in the house_number parameter.
        
        Response ordering:
        - *better match*, the results that match the given search parameters better are ranked higher, making names containing all words from search parameters appear on top of the results
        - *unique words*, the more unique words that are ranked higher than common ones, this prevent words like "van" or "de" pollute the ordering when they are searched on
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $trade_name - Name under which the organisation engages in commercial activity
            string $city - City
            string $street - Street
            string $postcode - Postcode
            int $house_number - House number
            string $house_number_addition - House number addition
            string $telephone_number - Telephone number
            string $domain_name - Domain name or emailaddress, when an emailaddress is given the domain part of that address is used
            boolean $strict_search - Boolean indicating if only strict matches should be returned, resulting in less matches.
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <DutchBusinessReferenceV2>
    */
    public function dutchBusinessSearchParametersV2($trade_name, $city, $street, $postcode, $house_number, $house_number_addition, $telephone_number, $domain_name, $strict_search, $page)
    {
        return $this->_client->dutchBusinessSearchParametersV2(['trade_name' => $trade_name, 'city' => $city, 'street' => $street, 'postcode' => $postcode, 'house_number' => $house_number, 'house_number_addition' => $house_number_addition, 'telephone_number' => $telephone_number, 'domain_name' => $domain_name, 'strict_search' => $strict_search, 'page' => $page]);
    }

    /*
        Find business establishments for a dossier number. Found dossiers are ordered by relevance,
        ensuring the establishments that match the search parameters best are listed at the top of the result list.
        When the dossier_number is omitted, the search behaves simular to the <dutchBusinessSearchParametersV2> method.
        
        Using the search parameters:
        - *trade_name* parameter will be used to search all business names for the dossiers, which include the trade name, legal name and alternative trade names.
        - *address* parameters are matched against both the correspondence and establishment addresses of the business.
        - *postbus* addresses can be found by specifying 'Postbus' as street, and specifying the postbus number in the house_number parameter.
        
        Response ordering:
        - *better match*, the results that match the given search parameters better are ranked higher, making names containing all words from search parameters appear on top of the results
        - *unique words*, more unique words are ranked higher than common ones. This prevent words like "van" or "de" pollute the ordering when they're searched on.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - Dossier number for the business
            string $trade_name - Name under which the organisation engages in commercial activity
            string $city - City
            string $street - Street
            string $postcode - Postcode
            int $house_number - House number
            string $house_number_addition - House number addition
            string $telephone_number - Telephone number
            string $domain_name - Domain name or emailaddress, when an emailaddress is given the domain part of that address is used
            boolean $strict_search - Boolean indicating if only strict matches should be returned, resulting in less matches.
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <DutchBusinessEstablishmentReference>
    */
    public function dutchBusinessSearch($dossier_number, $trade_name, $city, $street, $postcode, $house_number, $house_number_addition, $telephone_number, $domain_name, $strict_search, $page)
    {
        return $this->_client->dutchBusinessSearch(['dossier_number' => $dossier_number, 'trade_name' => $trade_name, 'city' => $city, 'street' => $street, 'postcode' => $postcode, 'house_number' => $house_number, 'house_number_addition' => $house_number_addition, 'telephone_number' => $telephone_number, 'domain_name' => $domain_name, 'strict_search' => $strict_search, 'page' => $page]);
    }

    /*
        Search for business establishments using a known identifier. Any combination of parameters may be specified.
        Only businesses matching all parameters will be returned.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - The Chamber of Commerce number
            string $establishment_number - The Establishment number
            string $rsin_number - The RSIN (`Rechtspersonen Samenwerkingsverbanden Informatie Nummer`) number
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <DutchBusinessEstablishmentReference>
    */
    public function dutchBusinessSearchEstablishments($dossier_number, $establishment_number, $rsin_number, $page)
    {
        return $this->_client->dutchBusinessSearchEstablishments(['dossier_number' => $dossier_number, 'establishment_number' => $establishment_number, 'rsin_number' => $rsin_number, 'page' => $page]);
    }

    /*
        Find business establishments based on postcode and house number.
        This method can return more matches than <dutchBusinessSearchParameters>.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $postcode - Postcode
            int $house_number - House number
            string $house_number_addition - House number addition
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <DutchBusinessReference>
    */
    public function dutchBusinessSearchPostcode($postcode, $house_number, $house_number_addition, $page)
    {
        return $this->_client->dutchBusinessSearchPostcode(['postcode' => $postcode, 'house_number' => $house_number, 'house_number_addition' => $house_number_addition, 'page' => $page]);
    }

    /*
        Search for businesses matching all of the given criteria.
        
        Either of these criteria can be left empty or 0. In that case, the criterium is not used.
        At least one of the criteria parameters must be supplied. At most 100 items may be supplied
        for the array parameters.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            stringArray $city - Array of cities. Businesses match if they are located in either of these cities, thus if the establishment
                    address is in one of these cities.
            stringArray $postcode - Array of postcodes or parts of postcodes. Bussinesses match if they are located in either of
                    these postcodes, or their postcode start with any of the given partial postcodes. Thus, if the establishment
                    address matches with one of the given postcodes. For example, the partial postcode "10" matches most of Amsterdam.
                    Note that it would make little sense to supply both city and postcode.
            stringArray $sbi - Array of SBI codes or partial SBI codes. Businesses match if they have either of the given SBI codes, or their SBI
                    code starts with the partial SBI code.
                    The CBS has a <http://www.cbs.nl/nl-NL/menu/methoden/classificaties/overzicht/sbi/sbi-2008/default.htm|list of SBI codes>
                    and their descriptions.
            boolean $primary_sbi_only - Match primary SBI only. A business may have up to three SBI codes assigned. If primary_sbi_only is true, businesses only match if
                    their main SBI code matches with one of the codes in the 'sbi' field. If primary_sbi_only is false, businesses are matched if
                    either of the three SBI codes match the 'sbi' field.
            intArray $legal_form - Array of integer legal form codes. Bussiness match if they have either of these legalforms.
                    A list of legal form codes can be found in the documentation of <DutchBusinessDossier>.
            int $employees_min - Minumum number of employees working at the business.
            int $employees_max - Maximum number of employees working at the business.
            string $economically_active - Indicates whether the businesses should be economically active.
                    active    -- Only economically active businesses match.
                    inactive  -- Only economically inactive businesses match.
            string $financial_status - Indicates the financial status of the businesses.
                    bankrupt  -- Only bankrupt businesses match.
                    dip       -- Only businesses which are debtor in possession match.
                    solvent   -- Only businesses which are neither bankrupt nor debtor in possession match.
                    insolvent -- Only businesses which are either bankrupt or debtor in posession match.
            string $changed_since - Date in yyyy-mm-dd format. Businesses match if the information about them changed on or after this date.
            string $new_since - Date in yyyy-mm-dd format. Only businesses which were added on or after this date are returned.
                    Note that this does not mean that the company was founded after this date. Companies may be founded and
                    only later be added to the DutchBusiness database.
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <DutchBusinessReference>.
    */
    public function dutchBusinessSearchSelection($city, $postcode, $sbi, $primary_sbi_only, $legal_form, $employees_min, $employees_max, $economically_active, $financial_status, $changed_since, $new_since, $page)
    {
        return $this->_client->dutchBusinessSearchSelection(['city' => $city, 'postcode' => $postcode, 'sbi' => $sbi, 'primary_sbi_only' => $primary_sbi_only, 'legal_form' => $legal_form, 'employees_min' => $employees_min, 'employees_max' => $employees_max, 'economically_active' => $economically_active, 'financial_status' => $financial_status, 'changed_since' => $changed_since, 'new_since' => $new_since, 'page' => $page]);
    }

    /*
        Look up a SBI ('Standaard Bedrijfs Indeling 2008') code. Returns the section and its description,
        and all levels of SBI codes and their description, according to the 17-04-2014 version.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $sbi_code - A SBI code, a number between 2 and 6 characters.
            string $language - the language of the resulted sbi code descriptions
                    nl		-- Dutch (default)
                    en		-- English
    

        Returns:
            out - A <DutchBusinessSBICodeInfo> .
    */
    public function dutchBusinessGetSBIDescription($sbi_code, $language)
    {
        return $this->_client->dutchBusinessGetSBIDescription(['sbi_code' => $sbi_code, 'language' => $language]);
    }

    /*
        Get an extract document in PDF, containing the available Chamber of Commerce data for a business.
        The document is generated using the business' `Online inzage uittreksel`.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - Chamber of Commerce number
            boolean $allow_caching - Determines whether a cached document may be returned:
                    false		-- The returned document is up-to-date.
                    true		-- The returned document may be older. The date of the document is included in the output, see <DutchBusinessExtractDocument>.
    

        Returns:
            out - A <DutchBusinessExtractDocument>.
    */
    public function dutchBusinessGetExtractDocument($dossier_number, $allow_caching)
    {
        return $this->_client->dutchBusinessGetExtractDocument(['dossier_number' => $dossier_number, 'allow_caching' => $allow_caching]);
    }

    /*
        Get the data from an extract document containing the available Chamber of Commerce data for a business.
        The document is generated using the business' `Online inzage uittreksel`.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - Chamber of Commerce number
            boolean $allow_caching - Determines whether a cached document may be returned:
                    false		-- The returned document is up-to-date.
                    true		-- The returned document may be older. The date of the document is included in the output, see <DutchBusinessExtractDocumentData>.
    

        Returns:
            out - A <DutchBusinessExtractDocumentData>.
    */
    public function dutchBusinessGetExtractDocumentData($dossier_number, $allow_caching)
    {
        return $this->_client->dutchBusinessGetExtractDocumentData(['dossier_number' => $dossier_number, 'allow_caching' => $allow_caching]);
    }

    /*
        Get the extract data and document for a business dossier
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - Chamber of Commerce number
    

        Returns:
            out - A <DutchBusinessExtractDocumentDataV2>.
    */
    public function dutchBusinessGetExtractDocumentDataV2($dossier_number)
    {
        return $this->_client->dutchBusinessGetExtractDocumentDataV2(['dossier_number' => $dossier_number]);
    }

    /*
        Get the legal extract data and document for a business dossier
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - Chamber of Commerce number
    

        Returns:
            out - A <DutchBusinessExtractDocumentDataV2>.
    */
    public function dutchBusinessGetLegalExtractDocumentDataV2($dossier_number)
    {
        return $this->_client->dutchBusinessGetLegalExtractDocumentDataV2(['dossier_number' => $dossier_number]);
    }

    /*
        Get a list of historical business-extract references for the given company or organisation collected by Webservices.nl.
        Each business-extract reference in the history contains a summary of the changes relative to the previous business-extract reference in the history.
        The business-extract history also contains an forecast that indicates whether changes have occured between the latest business-extract document and the current state or the organisation.
        When changes are detected the most recent document in the history probably does not represent the current state of the organisation.
        A realtime document can be retrieved using <dutchBusinessGetExtractDocumentData> or <dutchBusinessGetExtractDocument>.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - Chamber of Commerce number.
            date $period_start_date - The start date of the period of historic documents.
            date $period_end_date - The end date of the set period, can differ max one year from start date. [optional][default:today]
    

        Returns:
            out - A <DutchBusinessExtractHistory>.
    */
    public function dutchBusinessGetExtractHistory($dossier_number, $period_start_date, $period_end_date)
    {
        return $this->_client->dutchBusinessGetExtractHistory(['dossier_number' => $dossier_number, 'period_start_date' => $period_start_date, 'period_end_date' => $period_end_date]);
    }

    /*
        Get a list of historical business-extract references for the given company or organisation collected by Webservices.nl that
        contain changes compared to their previous retrieved extract.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - Chamber of Commerce number.
            date $period_start_date - The start date of the period of historic documents.
            date $period_end_date - The end date of the set period. [optional][default:today]
    

        Returns:
            out - A <DutchBusinessExtractHistory>.
    */
    public function dutchBusinessGetExtractHistoryChanged($dossier_number, $period_start_date, $period_end_date)
    {
        return $this->_client->dutchBusinessGetExtractHistoryChanged(['dossier_number' => $dossier_number, 'period_start_date' => $period_start_date, 'period_end_date' => $period_end_date]);
    }

    /*
        Retrieve a historical business-extract using a business-extract identifier. Business-extract identifiers can be found using <dutchBusinessGetExtractHistory>.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $extract_id - Business-extract identifier
    

        Returns:
            out - A <DutchBusinessExtractDocumentData>.
    */
    public function dutchBusinessGetExtractHistoryDocumentData($extract_id)
    {
        return $this->_client->dutchBusinessGetExtractHistoryDocumentData(['extract_id' => $extract_id]);
    }

    /*
        Retrieve a historical business-extract using a business-extract identifier. Business-extract identifiers can be found using <dutchBusinessGetExtractHistory>.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $extract_id - Business-extract identifier
    

        Returns:
            out - A <DutchBusinessExtractDocumentDataV2>.
    */
    public function dutchBusinessGetExtractHistoryDocumentDataV2($extract_id)
    {
        return $this->_client->dutchBusinessGetExtractHistoryDocumentDataV2(['extract_id' => $extract_id]);
    }

    /*
        Get a list of logged updates for a specific business dossier.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - Chamber of Commerce number.
            date $period_start_date - Period start date, in yyyy-mm-dd format.
            date $period_end_date - Period end date, in yyyy-mm-dd format. The max period is one year. [optional]
    

        Returns:
            out - A <DutchBusinessDossierHistory>.
    */
    public function dutchBusinessGetDossierHistory($dossier_number, $period_start_date, $period_end_date)
    {
        return $this->_client->dutchBusinessGetDossierHistory(['dossier_number' => $dossier_number, 'period_start_date' => $period_start_date, 'period_end_date' => $period_end_date]);
    }

    /*
        Retrieve information on the last change to a business establishment.
        
        This method can be used to check for updates on specific dossiers, regardless of whether requested dossiers are logged for the user.
        A <DutchBusinessUpdateReference> is returned for the most recent update, if any. The <DutchBusinessUpdateReference> contains the date of the
        latest update to the dossier, as well as the types of updates performed on that date.
        A fault message is returned if there have never been updates to the dossier. The same fault is returned if the dossier does not
        exist (or never existed).
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - Chamber of Commerce number
            string $establishment_number - Establishment number
            stringArray $update_types - The types of updates to consider. See <Update types> for a list of types.
                    If the type 'Test' is specified, a <DutchBusinessUpdateReference> is returned with DateLastUpdate set to today, its Update types will
                    contain all the types specified in the request.
    

        Returns:
            out 		- A <DutchBusinessUpdateReference> describing when the dossier was last updated and what types of updates occurred on that date.
    */
    public function dutchBusinessUpdateCheckDossier($dossier_number, $establishment_number, $update_types)
    {
        return $this->_client->dutchBusinessUpdateCheckDossier(['dossier_number' => $dossier_number, 'establishment_number' => $establishment_number, 'update_types' => $update_types]);
    }

    /*
        Retrieve dossier numbers for all dossiers changed since the given date.
        
        This method returns a <Patterns::{Type}PagedResult> of <DutchBusinessUpdateReference> entries, for all dossiers
        which were updated since the given changed_since date, and where the update was one of the given
        update_types. This method can be called periodically to obtain a list of recently updated dossiers. This
        list can then be checked against the list of locally stored dossiers, to determine which dossiers that
        the user has stored are changed and may be updated.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            date $changed_since - Date in YYYY-MM-DD format. All dossiers changed on or after this date are returned.
                    This date may not be more than 40 days ago.
            stringArray $update_types - The types of updates to consider. See <Update types> for a list of types. This method
                    supports the update type 'New' to retrieve dossiers which have been registered with the DutchBusiness since the changed_since date.
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <DutchBusinessUpdateReference> entries, with information on changed dossiers.
    */
    public function dutchBusinessUpdateGetChangedDossiers($changed_since, $update_types, $page)
    {
        return $this->_client->dutchBusinessUpdateGetChangedDossiers(['changed_since' => $changed_since, 'update_types' => $update_types, 'page' => $page]);
    }

    /*
        Returns a list of all dossiers that have been updated since they were last retrieved by the user
        (the user whose credentials are used to make the call).
        
        If a dossier is returned that is no longer of interest or has the update type 'Removed',
        calling <dutchBusinessUpdateRemoveDossier> prevents it from occurring in this method's output.
        If a dossier from the output list is retrieved using <dutchBusinessGetDossier>, a second call to
        <dutchBusinessUpdateGetDossiers> will not contain the dossier anymore.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            stringArray $update_types - A list specifying the types of updates that should be returned. See <Update types> for a list of types.
                    If the type 'Test' is specified, an example <DutchBusinessUpdateReference> is returned with DateLastUpdate set to today,
                    its Update types will contain all the types specified in the request.
            int $page - The page of results
    

        Returns:
            out 		- A <Patterns::{Type}PagedResult> of <DutchBusinessUpdateReference> entries.
            Every <DutchBusinessUpdateReference> describes a dossier, when it was last updated and what types of updates have occurred
            since the dossier was last retrieved by the user.
    */
    public function dutchBusinessUpdateGetDossiers($update_types, $page)
    {
        return $this->_client->dutchBusinessUpdateGetDossiers(['update_types' => $update_types, 'page' => $page]);
    }

    /*
        Add a dossier to the list of dossiers for which the user (the user whose credentials are used to make the call)
        wants to receive updates. After adding the dossier any future updates to the dossier can be retrieved using <dutchBusinessUpdateGetDossiers>.
        Before adding the dossier, call <dutchBusinessUpdateCheckDossier> to make sure you have the latest dossier version.
        
        You do not need to call this method if you have retrieved a dossier using <dutchBusinessGetDossier>, in which case it
        has been added automatically.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - Chamber of Commerce number
            string $establishment_number - Establishment number
    

        Returns:
            Nothing
    */
    public function dutchBusinessUpdateAddDossier($dossier_number, $establishment_number)
    {
        return $this->_client->dutchBusinessUpdateAddDossier(['dossier_number' => $dossier_number, 'establishment_number' => $establishment_number]);
    }

    /*
        Remove a dossier from the list of dossiers for which the user (the user whose credentials are used to make the call) wants to receive updates.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $dossier_number - Chamber of Commerce number
            string $establishment_number - Establishment number
    

        Returns:
            Nothing
    */
    public function dutchBusinessUpdateRemoveDossier($dossier_number, $establishment_number)
    {
        return $this->_client->dutchBusinessUpdateRemoveDossier(['dossier_number' => $dossier_number, 'establishment_number' => $establishment_number]);
    }

    /*
        Notice:
        This function is deprecated use <dutchBusinessSearchParametersV2> instead.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $trade_name - Name under which the organisation engages in commercial activity
            string $city - City
            string $street - Street
            string $postcode - Postcode
            int $house_number - House number
            string $house_number_addition - House number addition
            string $telephone_number - Telephone number
            string $domain_name - Domain name or emailaddress, when an emailaddress is given the domain part of that address is used
            boolean $strict_search - Boolean indicating if only strict matches should be returned, resulting in less matches.
            int $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <DutchBusinessReference>
    */
    public function dutchBusinessSearchParameters($trade_name, $city, $street, $postcode, $house_number, $house_number_addition, $telephone_number, $domain_name, $strict_search, $page)
    {
        return $this->_client->dutchBusinessSearchParameters(['trade_name' => $trade_name, 'city' => $city, 'street' => $street, 'postcode' => $postcode, 'house_number' => $house_number, 'house_number_addition' => $house_number_addition, 'telephone_number' => $telephone_number, 'domain_name' => $domain_name, 'strict_search' => $strict_search, 'page' => $page]);
    }

    /*
        Retrieve information about a vehicle registration.
        
        This method returns data on the fiscal price, power, environmental impact, status for a car as can be found
        at the dutch Department for Transport (RDW)
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $license_plate - The dutch license plate
    

        Returns:
            out				- A <DutchVehicle> entry containing the information about the vehicle.
    */
    public function dutchVehicleGetVehicle($license_plate)
    {
        return $this->_client->dutchVehicleGetVehicle(['license_plate' => $license_plate]);
    }

    /*
        Retrieve information about a vehicle purchase/catalog price.
        
        This method returns (recalculated) purchase and vehicle reference information, useful to establish the
        insurance amount.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $license_plate - The dutch license plate
    

        Returns:
            out				- A <DutchVehiclePurchaseReference> entry containg the information about the vehicle purchase price
    */
    public function dutchVehicleGetPurchaseReference($license_plate)
    {
        return $this->_client->dutchVehicleGetPurchaseReference(['license_plate' => $license_plate]);
    }

    /*
        Retrieve information about the ownership of a vehicle.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $license_plate - The dutch license plate
    

        Returns:
            out				- A <DutchVehicleOwnerHistory> entry containing the registered ownerdata.
    */
    public function dutchVehicleGetOwnerHistory($license_plate)
    {
        return $this->_client->dutchVehicleGetOwnerHistory(['license_plate' => $license_plate]);
    }

    /*
        Retrieve information about the current market value of a vehicle.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $license_plate - The dutch license plate
    

        Returns:
            out				- A <DutchVehicleMarketValue> entry containing the registered ownerdata.
    */
    public function dutchVehicleGetMarketValue($license_plate)
    {
        return $this->_client->dutchVehicleGetMarketValue(['license_plate' => $license_plate]);
    }

    /*
        Provides a credit score for a person identified by a set of parameters.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $last_name - The last name of the person
            string $initials - The initials
            string $surname_prefix - The surname prefix, like 'van' or 'de', optional
            string $gender - Gender of the person. `M` or `F`
            string $birth_date - Birth date in the format yyyy-mm-dd
            string $street - Street part of the address
            string $house_number - House number, optionally including a house number addition
            string $postcode - Dutch postcode in the format 1234AB
            string $phone_number - Home phone number, only numeric characters (e.g. 0201234567), may be empty.
    

        Returns:
            out		- An <EDRScore>
    */
    public function edrGetScore($last_name, $initials, $surname_prefix, $gender, $birth_date, $street, $house_number, $postcode, $phone_number)
    {
        return $this->_client->edrGetScore(['last_name' => $last_name, 'initials' => $initials, 'surname_prefix' => $surname_prefix, 'gender' => $gender, 'birth_date' => $birth_date, 'street' => $street, 'house_number' => $house_number, 'postcode' => $postcode, 'phone_number' => $phone_number]);
    }

    /*
        Returns the coordinates in the RD system of the neighborhood,
        given the neighborhood code.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationNeighborhoodCoordinatesRD
    
        Parameters:
            string $nbcode - Neighborhoodcode to find the location of
    

        Returns:
            coordinates   - A <RDCoordinates> entry.
    */
    public function geoLocationNeighborhoodCoordinatesRD($nbcode)
    {
        return $this->_client->geoLocationNeighborhoodCoordinatesRD(['nbcode' => $nbcode]);
    }

    /*
        Returns the coordinates of the given postcode in the RD system.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationPostcodeCoordinatesRD
    
        Parameters:
            string $postcode - Postcode to find the location of
    

        Returns:
            coordinates   - A <RDCoordinates> entry.
    */
    public function geoLocationPostcodeCoordinatesRD($postcode)
    {
        return $this->_client->geoLocationPostcodeCoordinatesRD(['postcode' => $postcode]);
    }

    /*
        Returns the coordinates in the latitude/longitude system of the neighborhood,
        given the neighborhood code.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationNeighborhoodCoordinatesLatLon
    
        Parameters:
            string $nbcode - Neighborhoodcode to find the location of
    

        Returns:
            coordinates   - A <LatLonCoordinates> entry.
    */
    public function geoLocationNeighborhoodCoordinatesLatLon($nbcode)
    {
        return $this->_client->geoLocationNeighborhoodCoordinatesLatLon(['nbcode' => $nbcode]);
    }

    /*
        Returns the coordinates of the given postcode in degrees of latitude/longitude.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationPostcodeCoordinatesLatLon
    
        Parameters:
            string $postcode - Postcode to find the location of
    

        Returns:
            coordinates   - A <LatLonCoordinates> entry.
    */
    public function geoLocationPostcodeCoordinatesLatLon($postcode)
    {
        return $this->_client->geoLocationPostcodeCoordinatesLatLon(['postcode' => $postcode]);
    }

    /*
        Returns the coordinates of the given address in degrees of latitude/longitude.
        You may either specify an address using postcode and house number, or using city, street and house number.
        Either postcode or city is required.
        
        When the city and street parameters are specified the city name and street name that were matched are returned in the result.
        
        If a house number is specified its location is interpolated using coordinates of the address range it
        belongs to. Accuracy may vary depending on the actual distribution of addresses in the range.
        For the most accurate house number coordinates, use <Kadaster::kadasterAddressCoordinates>.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationAddressCoordinatesLatLon
    
        Parameters:
            string $postcode - Address postcode
            string $city - Address city
            string $street - Address street
            int $houseno - Address house number
    

        Returns:
            coordinates   - A <LatLonCoordinatesMatch> entry.
    */
    public function geoLocationAddressCoordinatesLatLon($postcode, $city, $street, $houseno)
    {
        return $this->_client->geoLocationAddressCoordinatesLatLon(['postcode' => $postcode, 'city' => $city, 'street' => $street, 'houseno' => $houseno]);
    }

    /*
        Returns the coordinates of the given address in the RD system.
        You may either specify an address using postcode and house number, or using city, street and house number.
        Either postcode or city is required.
        
        When the city and street parameters are specified the city name and street name that were matched are returned in the result.
        
        If a house number is specified its location is interpolated using coordinates of the address range it
        belongs to. Accuracy may vary depending on the actual distribution of addresses in the range.
        For the most accurate house number coordinates, use <Kadaster::kadasterAddressCoordinates>.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationAddressCoordinatesRD
    
        Parameters:
            string $postcode - Address postcode
            string $city - Address city
            string $street - Address street
            int $houseno - Address house number
    

        Returns:
            coordinates   - A <RDCoordinatesMatch> entry.
    */
    public function geoLocationAddressCoordinatesRD($postcode, $city, $street, $houseno)
    {
        return $this->_client->geoLocationAddressCoordinatesRD(['postcode' => $postcode, 'city' => $city, 'street' => $street, 'houseno' => $houseno]);
    }

    /*
        Returns the postcode of the address closest to the specified
        latitude/longitude coordinate in the Netherlands
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationLatLonToPostcode
    
        Parameters:
            float $latitude - Latitude of the postcode
            float $longitude - Longitude of the postcode
    

        Returns:
            postcode - The postcode closest to the coordinates.
    */
    public function geoLocationLatLonToPostcode($latitude, $longitude)
    {
        return $this->_client->geoLocationLatLonToPostcode(['latitude' => $latitude, 'longitude' => $longitude]);
    }

    /*
        Returns the address and geoLocation info closest to the specified
        latitude/longitude coordinate in the Netherlands
        
        This method differs from geoLocationLatLonToAddress in that it is more precise:
        it uses data with house number precision, instead of house number range precision.
        This means that a specific house number is returned instead of a range, and that
        the returned address is typically closer to the coordinate than with geoLocationLatLonToAddress.
        Note that this method may return a different street than geoLocationLatLonToAddress.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationLatLonToAddressV2
    
        Parameters:
            float $latitude - Latitude of the location
            float $longitude - Longitude of the location
    

        Returns:
            address - A <GeoLocationAddressV2> entry.
    */
    public function geoLocationLatLonToAddressV2($latitude, $longitude)
    {
        return $this->_client->geoLocationLatLonToAddressV2(['latitude' => $latitude, 'longitude' => $longitude]);
    }

    /*
        Returns the postcode of the address closest to the specified
        Rijksdriehoeksmeting coordinate in the Netherlands
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationRDToPostcode
    
        Parameters:
            int $x - x part of the RD coordinate
            int $y - y part of the RD coordinate
    

        Returns:
            postcode - The postcode closest to the coordinates.
    */
    public function geoLocationRDToPostcode($x, $y)
    {
        return $this->_client->geoLocationRDToPostcode(['x' => $x, 'y' => $y]);
    }

    /*
        Returns the address and geoLocation info closest to the specified
        Rijksdriehoeksmeting X/Y coordinate in the Netherlands.
        
        This method differs from geoLocationLatLonToAddress in that it is more precise:
        it uses data with house number precision, instead of house number range precision.
        This means that a specific house number is returned instead of a range, and that
        the returned address is typically closer to the coordinate than with geoLocationRDToAddress.
        Note that this method may return a different street than geoLocationRDToAddress.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationRDToAddressV2
    
        Parameters:
            int $x - rd X of the location
            int $y - rd Y of the location
    

        Returns:
            address - A <GeoLocationAddressV2> entry.
    */
    public function geoLocationRDToAddressV2($x, $y)
    {
        return $this->_client->geoLocationRDToAddressV2(['x' => $x, 'y' => $y]);
    }

    /*
        Returns the coordinates of the given postcode in degrees of latitude/longitude.
        Most countries are supported by this function.
        Accuracy of the result may vary between countries.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationInternationalPostcodeCoordinatesLatLon
    
        Parameters:
            string $postcode - Postcode to find the location of (postcode format varies depending on the country specified)
            string $country - Country of the address. Country can be specified by using ISO3 country codes (recommended). Complete country names may not always work.
    

        Returns:
            coordinates   - A <LatLonCoordinates> entry.
    */
    public function geoLocationInternationalPostcodeCoordinatesLatLon($postcode, $country)
    {
        return $this->_client->geoLocationInternationalPostcodeCoordinatesLatLon(['postcode' => $postcode, 'country' => $country]);
    }

    /*
        Returns the coordinates of the given address in degrees of latitude/longitude.
        Most countries are supported by this function. Accuracy of the result may vary between countries.
        Since the street and city have to contain the complete name and since this method acts with international data,
        we recommend to use <geoLocationInternationalPostcodeCoordinatesLatLon> if you know the postcode, since working with postcodes is
        less error prone.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationInternationalAddressCoordinatesLatLon
    
        Parameters:
            string $street - Complete street name. Street name may not be abbreviated, but may be empty.
            int $houseno - House number
            string $city - Complete city name. City name may not be abbreviated, but may be empty.
            string $province - Province, state, district (depends on country, may not be abbreviated). Ignored if not exactly matched.
            string $country - Country of the address. Country can be specified by using ISO3 country codes (recommended).
                    Complete country names may not always work.
            string $language - Language used for input and preferred language for the output.
                    Specify the language in Dutch, English or the local language.
                    Depending on the amount of available data and the precision of the result,
                    the output might not match the language requested.
    

        Returns:
            coordinates   - A <LatLonCoordinatesInternationalAddress> entry.
    */
    public function geoLocationInternationalAddressCoordinatesLatLon($street, $houseno, $city, $province, $country, $language)
    {
        return $this->_client->geoLocationInternationalAddressCoordinatesLatLon(['street' => $street, 'houseno' => $houseno, 'city' => $city, 'province' => $province, 'country' => $country, 'language' => $language]);
    }

    /*
        Returns the coordinates of the given address in degrees of latitude/longitude.
        Most countries are supported by this function. Accuracy of the result may vary between countries.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationInternationalAddressCoordinatesLatLonV2
    
        Parameters:
            string $country - Country of the address. Country can be specified by using ISO3 country codes (recommended).
                    Complete country names may not always work.
            string $postalcode - Postalcode
            int $houseno - House number
            string $street - Complete street name. Street name may not be abbreviated, but may be empty.
            string $city - Complete city name. City name may not be abbreviated, but may be empty.
            string $province - Province, state, district (depends on country, may not be abbreviated). Ignored if not exactly matched.
            float $matchrate - The minimum matchlevel the returned search-results range [0-100]
            string $language - Language used for input and preferred language for the output.
                    Depending on the amount of available data and the precision of the result,
                    the output might not match the language requested.
    

        Returns:
            coordinates   - A <LatLonCoordinatesInternationalAddress> entry.
    */
    public function geoLocationInternationalAddressCoordinatesLatLonV2($country, $postalcode, $houseno, $street, $city, $province, $matchrate, $language)
    {
        return $this->_client->geoLocationInternationalAddressCoordinatesLatLonV2(['country' => $country, 'postalcode' => $postalcode, 'houseno' => $houseno, 'street' => $street, 'city' => $city, 'province' => $province, 'matchrate' => $matchrate, 'language' => $language]);
    }

    /*
        Returns the address and geoLocation info closest to the specified
        latitude/longitude coordinate.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationInternationalLatLonToAddress
    
        Parameters:
            float $latitude - Latitude of the location
            float $longitude - Longitude of the location
    

        Returns:
            address - A <GeoLocationInternationalAddress> entry.
    */
    public function geoLocationInternationalLatLonToAddress($latitude, $longitude)
    {
        return $this->_client->geoLocationInternationalLatLonToAddress(['latitude' => $latitude, 'longitude' => $longitude]);
    }

    /*
        Returns the estimated distance in meters (in a direct line)
        between two neighborhoods, given the neighborhood codes.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationNeighborhoodDistance
    
        Parameters:
            string $nbcodefrom - Neighborhoodcode of the first neighborhood
            string $nbcodeto - Neighborhoodcode of the second neighborhood
    

        Returns:
            distance      - The distance in meters.
    */
    public function geoLocationNeighborhoodDistance($nbcodefrom, $nbcodeto)
    {
        return $this->_client->geoLocationNeighborhoodDistance(['nbcodefrom' => $nbcodefrom, 'nbcodeto' => $nbcodeto]);
    }

    /*
        Returns the estimated distance in meters (in a direct line)
        between two postcodes.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationPostcodeDistance
    
        Parameters:
            string $postcodefrom - First postcode
            string $postcodeto - Second postcode
    

        Returns:
            distance      - The distance in meters.
    */
    public function geoLocationPostcodeDistance($postcodefrom, $postcodeto)
    {
        return $this->_client->geoLocationPostcodeDistance(['postcodefrom' => $postcodefrom, 'postcodeto' => $postcodeto]);
    }

    /*
        Returns the distance in meters (in a direct line) between two latitude/longitude coordinates. Computed by using the Haversine formula, which is accurate as long as the locations are not antipodal (at the other side of the Earth).
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationHaversineDistance
    
        Parameters:
            float $latitude_coord_1 - Latitude of the first location
            float $longitude_coord_1 - Longitude of the first location
            float $latitude_coord_2 - Latitude of the second location
            float $longitude_coord_2 - Longitude of the second location
            boolean $in_radians - Indicate if input is in radians (otherwise they are interpreted as degrees)
    

        Returns:
            distance      - The distance in meters.
    */
    public function geoLocationHaversineDistance($latitude_coord_1, $longitude_coord_1, $latitude_coord_2, $longitude_coord_2, $in_radians)
    {
        return $this->_client->geoLocationHaversineDistance(['latitude_coord_1' => $latitude_coord_1, 'longitude_coord_1' => $longitude_coord_1, 'latitude_coord_2' => $latitude_coord_2, 'longitude_coord_2' => $longitude_coord_2, 'in_radians' => $in_radians]);
    }

    /*
        Returns a given neighborhoodcode list sorted in order of increasing
        distance from a given neighborhood
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationDistanceSortedNeighborhoodCodes
    
        Parameters:
            string $nbcodefrom - Neighborhoodcode to sort the list on
            stringArray $nbcodes - Array of neighborhood codes to sort using increasing
                    distance to nbcodefrom.
    

        Returns:
            nbcodes      - A <Patterns::{Type}Array> of <SortedPostcode> entries.
    */
    public function geoLocationDistanceSortedNeighborhoodCodes($nbcodefrom, $nbcodes)
    {
        return $this->_client->geoLocationDistanceSortedNeighborhoodCodes(['nbcodefrom' => $nbcodefrom, 'nbcodes' => $nbcodes]);
    }

    /*
        Returns a list of neighborhood codes sorted in order of increasing distance
        from a given neighborhood, within a given radius (in meters).
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationDistanceSortedNeighborhoodCodesRadius
    
        Parameters:
            string $nbcodefrom - Neighborhoodcode at the center of the radius
            integer $radius - Radius from nbcodefrom to search in, in meters
            integer $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <SortedPostcode> entries.
    */
    public function geoLocationDistanceSortedNeighborhoodCodesRadius($nbcodefrom, $radius, $page)
    {
        return $this->_client->geoLocationDistanceSortedNeighborhoodCodesRadius(['nbcodefrom' => $nbcodefrom, 'radius' => $radius, 'page' => $page]);
    }

    /*
        Returns a list of postcodes sorted in order of increasing distance
        from a given postcode, within a given radius (in meters).
        If the radius is larger than 1500 meters, the result will be based on neighborhood codes.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationDistanceSortedPostcodesRadius
    
        Parameters:
            string $postcodefrom - Postcode at the center of the radius
            integer $radius - Radius from postcodefrom to search in, in meters
            integer $page - Page to retrieve, pages start counting at 1
    

        Returns:
            out - A <Patterns::{Type}PagedResult> of <SortedPostcode> entries.
    */
    public function geoLocationDistanceSortedPostcodesRadius($postcodefrom, $radius, $page)
    {
        return $this->_client->geoLocationDistanceSortedPostcodesRadius(['postcodefrom' => $postcodefrom, 'radius' => $radius, 'page' => $page]);
    }

    /*
        Convert a latitude/longitude coordinate to a RD ('Rijksdriehoeksmeting') coordinate.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationLatLonToRD
    
        Parameters:
            float $latitude - Latitude
            float $longitude - Longitude
    

        Returns:
            out	- A <RDCoordinates> entry
    */
    public function geoLocationLatLonToRD($latitude, $longitude)
    {
        return $this->_client->geoLocationLatLonToRD(['latitude' => $latitude, 'longitude' => $longitude]);
    }

    /*
        Convert a RD ('Rijksdriehoeksmeting') coordinate to a latitude/longitude coordinate.
    
        http://webview.webservices.nl/documentation/files/service_geolocation-class-php.html#Geolocation.geoLocationRDToLatLon
    
        Parameters:
            int $x - x part of the RD coordinate
            int $y - y part of the RD coordinate
    

        Returns:
            out	- A <LatLonCoordinates> entry
    */
    public function geoLocationRDToLatLon($x, $y)
    {
        return $this->_client->geoLocationRDToLatLon(['x' => $x, 'y' => $y]);
    }

    /*
        Retrieve a Graydon credit report of a company registered in the Netherlands.
    
        http://webview.webservices.nl/documentation/files/service_graydoncredit-class-php.html#Graydon_Credit.graydonCreditGetReport
    
        Parameters:
            integer $graydon_company_id - 9 Digit Graydon company identification number.
                    See <Company Test Identifiers> for a list of free test reports.
            string $document - Specify to retrieve an extra document with an excerpt of the data. Currently unused. Possible values:
                    [empty string] -- Return no extra document.
    

        Returns:
            out - A <GraydonCreditReport>.
    */
    public function graydonCreditGetReport($graydon_company_id, $document)
    {
        return $this->_client->graydonCreditGetReport(['graydon_company_id' => $graydon_company_id, 'document' => $document]);
    }

    /*
        Search international Graydon credit report databases for a company using an identifier.
    
        http://webview.webservices.nl/documentation/files/service_graydoncredit-class-php.html#Graydon_Credit.graydonCreditSearchIdentification
    
        Parameters:
            string $company_id - Company identification
            string $company_id_type - Identification type. Supported:
                    graydon -- 9 digit Graydon company id
                    kvk     -- 8 digit Dutch Chamber of Commerce (KvK) dossier number, without the sub dossier number
            string $country_iso2 - Country where the company is registered, country name, ISO 3166 alpha 2 code. Supported countries:
                    nl -- The Netherlands
    

        Returns:
            out - A <Patterns::{Type}Array> of <GraydonReference> elements.
    */
    public function graydonCreditSearchIdentification($company_id, $company_id_type, $country_iso2)
    {
        return $this->_client->graydonCreditSearchIdentification(['company_id' => $company_id, 'company_id_type' => $company_id_type, 'country_iso2' => $country_iso2]);
    }

    /*
        Search the international Graydon credit report database for a company by its name.
    
        http://webview.webservices.nl/documentation/files/service_graydoncredit-class-php.html#Graydon_Credit.graydonCreditSearchName
    
        Parameters:
            string $company_name - Required. Company name, trade name or business name.
            string $residence - Name of the city or region
            string $country_iso2 - Country where the company is registered, country name, ISO 3166 alpha 2 code. Supported countries:
                    nl -- The Netherlands
    

        Returns:
            out - A <Patterns::{Type}Array> of <GraydonReference> elements.
    */
    public function graydonCreditSearchName($company_name, $residence, $country_iso2)
    {
        return $this->_client->graydonCreditSearchName(['company_name' => $company_name, 'residence' => $residence, 'country_iso2' => $country_iso2]);
    }

    /*
        Search international Graydon credit report database for a company using its postcode.
    
        http://webview.webservices.nl/documentation/files/service_graydoncredit-class-php.html#Graydon_Credit.graydonCreditSearchPostcode
    
        Parameters:
            string $postcode - Postcode.
            int $house_no - House number of the address. Requires input of postcode parameter.
            string $telephone_no - Telephone number.
            string $country_iso2 - Country where the company is registered, country name, ISO 3166 alpha 2 code. Supported countries:
                    nl -- The Netherlands
    

        Returns:
            out - A <Patterns::{Type}Array> of <GraydonReference> elements.
    */
    public function graydonCreditSearchPostcode($postcode, $house_no, $telephone_no, $country_iso2)
    {
        return $this->_client->graydonCreditSearchPostcode(['postcode' => $postcode, 'house_no' => $house_no, 'telephone_no' => $telephone_no, 'country_iso2' => $country_iso2]);
    }

    /*
        Retrieve a Graydon pd ratings and credit flag of a company registered in the Netherlands.
        If an alarm code is set, no values are returned. Use <graydonCreditGetReport> to retrieve more information about the alarm/calamity.
    
        http://webview.webservices.nl/documentation/files/service_graydoncredit-class-php.html#Graydon_Credit.graydonCreditQuickscan
    
        Parameters:
            integer $graydon_company_id - 9 Digit Graydon company identification number.
                    See <Company Test Identifiers> for a list of free test reports.
    

        Returns:
            out - A <GraydonCreditReportQuickscan>.
    */
    public function graydonCreditQuickscan($graydon_company_id)
    {
        return $this->_client->graydonCreditQuickscan(['graydon_company_id' => $graydon_company_id]);
    }

    /*
        Retrieve various Graydon credit ratings of a company registered in the Netherlands.
        If an alarm code is set, no values are returned. Use <graydonCreditGetReport> to retrieve more information about the alarm/calamity.
    
        http://webview.webservices.nl/documentation/files/service_graydoncredit-class-php.html#Graydon_Credit.graydonCreditRatings
    
        Parameters:
            integer $graydon_company_id - 9 Digit Graydon company identification number.
                    See <Company Test Identifiers> for a list of free test reports.
    

        Returns:
            out - A <GraydonCreditReportRatings>.
    */
    public function graydonCreditRatings($graydon_company_id)
    {
        return $this->_client->graydonCreditRatings(['graydon_company_id' => $graydon_company_id]);
    }

    /*
        Retrieve the BTW (VAT) number of a company registered in the Netherlands.
        If an alarm code is set, no values are returned. Use <graydonCreditGetReport> to retrieve more information about the alarm/calamity.
    
        http://webview.webservices.nl/documentation/files/service_graydoncredit-class-php.html#Graydon_Credit.graydonCreditVatNumber
    
        Parameters:
            integer $graydon_company_id - 9 Digit Graydon company identification number.
                    See <Company Test Identifiers> for a list of free test reports.
    

        Returns:
            out - A <GraydonCreditReportVatNumber>.
    */
    public function graydonCreditVatNumber($graydon_company_id)
    {
        return $this->_client->graydonCreditVatNumber(['graydon_company_id' => $graydon_company_id]);
    }

    /*
        Retrieve top-parent, parent and sibling companies of a company registered in the Netherlands.
        If an alarm code is set, no values are returned. Use <graydonCreditGetReport> to retrieve more information about the alarm/calamity.
    
        http://webview.webservices.nl/documentation/files/service_graydoncredit-class-php.html#Graydon_Credit.graydonCreditCompanyLiaisons
    
        Parameters:
            integer $graydon_company_id - 9 Digit Graydon company identification number.
                    See <Company Test Identifiers> for a list of free test reports.
    

        Returns:
            out - A <GraydonCreditReportCompanyLiaisons>.
    */
    public function graydonCreditCompanyLiaisons($graydon_company_id)
    {
        return $this->_client->graydonCreditCompanyLiaisons(['graydon_company_id' => $graydon_company_id]);
    }

    /*
        Retrieve information on the management positions in a company registered in the Netherlands.
        If an alarm code is set, no values are returned. Use <graydonCreditGetReport> to retrieve more information about the alarm/calamity.
    
        http://webview.webservices.nl/documentation/files/service_graydoncredit-class-php.html#Graydon_Credit.graydonCreditManagement
    
        Parameters:
            integer $graydon_company_id - 9 Digit Graydon company identification number.
                    See <Company Test Identifiers> for a list of free test reports.
    

        Returns:
            out - A <GraydonCreditReportManagement>.
    */
    public function graydonCreditManagement($graydon_company_id)
    {
        return $this->_client->graydonCreditManagement(['graydon_company_id' => $graydon_company_id]);
    }

    /*
        This method is suited to handle data entry where only partial address information
        is provided. Based on the partial information a list of up to 20 addresses is suggested,
        saving significant key strokes when entering address data.
        
        Returns address suggestions related to the address information
        given. Suggestions are ranked based on a matching percentage.
        Per field status indications are also returned for every suggestion.
        Any parameter may be left empty, apart from the country parameter.
    
        http://webview.webservices.nl/documentation/files/service_internationaladdress-class-php.html#International_Address.internationalAddressSearchV2
    
        Parameters:
            string $organization - Name of the company or organisation at the address
            string $building - Building or subbuilding name
            string $street - Street search phrase
            string $housenr - House number search phrase
            string $pobox - PO box search phrase
            string $locality - District or municipality search phrase
            string $postcode - Postalcode search phrase
            string $province - Province search phrase
            string $country - Country of the address, required. Accepts ISO3 country codes.
            string $language - Language in which the results are returned, see <Preferred Language>.
            string $country_format - The format in which the country is returned, see <Country Format>.
    

        Returns:
            out - A <InternationalAddressSearchV2Result> entry.
    */
    public function internationalAddressSearchV2($organization, $building, $street, $housenr, $pobox, $locality, $postcode, $province, $country, $language, $country_format)
    {
        return $this->_client->internationalAddressSearchV2(['organization' => $organization, 'building' => $building, 'street' => $street, 'housenr' => $housenr, 'pobox' => $pobox, 'locality' => $locality, 'postcode' => $postcode, 'province' => $province, 'country' => $country, 'language' => $language, 'country_format' => $country_format]);
    }

    /*
        This method expects an address that is already more or less complete. It checks the correctness
        of the specified address, completing it if possible. If suggestions can be generated they will be
        returned as well.
        
        Returns address suggestions related to the address information
        given. Suggestions are ranked based on a matching percentage.
        Per field status indications are also returned for every suggestion.
        Any parameter may be left empty, apart from the country parameter.
    
        http://webview.webservices.nl/documentation/files/service_internationaladdress-class-php.html#International_Address.internationalAddressSearchInteractive
    
        Parameters:
            string $organization - Name of the company or organisation at the address
            string $building - Building or subbuilding name
            string $street - Street search phrase
            string $housenr - House number search phrase
            string $pobox - PO box search phrase
            string $locality - District or municipality search phrase
            string $postcode - Postalcode search phrase
            string $province - Province search phrase
            string $country - Country of the address, required. Accepts ISO3 country codes.
            string $language - Language in which the results are returned, see <Preferred Language>.
            string $country_format - The format in which the country is returned, see <Country Format>.
    

        Returns:
            out - A <InternationalAddressSearchV2Result> entry.
    */
    public function internationalAddressSearchInteractive($organization, $building, $street, $housenr, $pobox, $locality, $postcode, $province, $country, $language, $country_format)
    {
        return $this->_client->internationalAddressSearchInteractive(['organization' => $organization, 'building' => $building, 'street' => $street, 'housenr' => $housenr, 'pobox' => $pobox, 'locality' => $locality, 'postcode' => $postcode, 'province' => $province, 'country' => $country, 'language' => $language, 'country_format' => $country_format]);
    }

    /*
        Returns the coordinates of the given address in both the RD system and the latitude/longitude system.
        The lat/lon coordinates are derived from the RD coordinates.
        The address may be specified by giving the postcode, house number & house number addition or by giving the cityname, streetname, house number & house number addition.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterAddressCoordinates
    
        Parameters:
            string $postcode - Address postcode
            string $city - Address city
            string $street - Address street
            int $houseno - Address house number
            string $housenoaddition - Address house number addition
    

        Returns:
            coordinates   - A <KadasterCoordinates> entry.
    */
    public function kadasterAddressCoordinates($postcode, $city, $street, $houseno, $housenoaddition)
    {
        return $this->_client->kadasterAddressCoordinates(['postcode' => $postcode, 'city' => $city, 'street' => $street, 'houseno' => $houseno, 'housenoaddition' => $housenoaddition]);
    }

    /*
        Find a 'Eigendomsbericht' by parcel details.
        Returns the result in a file of the specified format.
        If the input matches more than one parcel, a list of the parcels found is returned instead.
        Sectie, perceelnummer and the code or name of the municipality are required.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterEigendomsBerichtDocumentPerceel
    
        Parameters:
            string $gemeentecode - Municipality code
            string $gemeentenaam - Municipality name. See <kadasterValueListGetMunicipalities> for possible values.
            string $sectie - Section code
            string $perceelnummer - Parcel number
            string $relatiecode - Indicates object relation type, set if object is part of another parcel. If relatiecode is specified, volgnummer should be specified as well. Allowed values: 'A', 'D', or empty.
            string $volgnummer - Object index number, set if object is part of another parcel
            string $format - Filetype of the result. The result will always be encoded in base 64. If an image format is requested a conversion is performed on our servers, which might
                    cause the response to be delayed for large documents. We recommend using a longer timeout setting for such requests. Supported formats:
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
    

        Returns:
            eigendomsberichtresultaat - A <BerichtObjectDocumentResultaat> entry.
    */
    public function kadasterEigendomsBerichtDocumentPerceel($gemeentecode, $gemeentenaam, $sectie, $perceelnummer, $relatiecode, $volgnummer, $format)
    {
        return $this->_client->kadasterEigendomsBerichtDocumentPerceel(['gemeentecode' => $gemeentecode, 'gemeentenaam' => $gemeentenaam, 'sectie' => $sectie, 'perceelnummer' => $perceelnummer, 'relatiecode' => $relatiecode, 'volgnummer' => $volgnummer, 'format' => $format]);
    }

    /*
        Find a 'Eigendomsbericht' by postcode and house number.
        Returns the result in a file of the specified format.
        If the input matches more than one parcel, a list of the parcels found is returned instead.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterEigendomsBerichtDocumentPostcode
    
        Parameters:
            string $postcode - Address postcode
            int $huisnummer - Address house number
            string $huisnummer_toevoeging - Address house number addition
            string $format - Filetype of the result. The result will always be encoded in base 64. If an image format is requested a conversion is performed on our servers, which might
                    cause the response to be delayed for large documents. We recommend using a longer timeout setting for such requests. Supported formats:
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
    

        Returns:
            eigendomsberichtresultaat - A <BerichtObjectDocumentResultaat> entry.
    */
    public function kadasterEigendomsBerichtDocumentPostcode($postcode, $huisnummer, $huisnummer_toevoeging, $format)
    {
        return $this->_client->kadasterEigendomsBerichtDocumentPostcode(['postcode' => $postcode, 'huisnummer' => $huisnummer, 'huisnummer_toevoeging' => $huisnummer_toevoeging, 'format' => $format]);
    }

    /*
        Find a 'Eigendomsbericht' by parcel details.
        Returns the result as a <BerichtObjectResultaatV2>.
        In addition to the structured result, a file in the PDF format is returned.
        If the input matches more than one parcel, a list of the parcels found is returned instead.
        Sectie, perceelnummer and the code or name of the municipality are required.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterEigendomsBerichtPerceelV2
    
        Parameters:
            string $gemeentecode - Municipality code
            string $gemeentenaam - Municipality name. See <kadasterValueListGetMunicipalities> for possible values.
            string $sectie - Section code
            string $perceelnummer - Parcel number
            string $relatiecode - Indicates object relation type, set if object is part of another parcel. If relatiecode is specified, volgnummer should be specified as well. Allowed values: 'A', 'D', or empty.
            string $volgnummer - Object index number, set if object is part of another parcel
    

        Returns:
            eigendomsberichtresultaat - A <BerichtObjectResultaatV2> entry.
    */
    public function kadasterEigendomsBerichtPerceelV2($gemeentecode, $gemeentenaam, $sectie, $perceelnummer, $relatiecode, $volgnummer)
    {
        return $this->_client->kadasterEigendomsBerichtPerceelV2(['gemeentecode' => $gemeentecode, 'gemeentenaam' => $gemeentenaam, 'sectie' => $sectie, 'perceelnummer' => $perceelnummer, 'relatiecode' => $relatiecode, 'volgnummer' => $volgnummer]);
    }

    /*
        Find a 'Eigendomsbericht' by postcode and house number.
        Returns the result as a <BerichtObjectResultaatV2>.
        In addition to the structured result, a file in the PDF format is returned.
        If the input matches more than one parcel, a list of the parcels found is returned instead.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterEigendomsBerichtPostcodeV2
    
        Parameters:
            string $postcode - Address postcode
            int $huisnummer - Address house number
            string $huisnummer_toevoeging - Address house number addition
    

        Returns:
            eigendomsberichtresultaat - A <BerichtObjectResultaatV2> entry.
    */
    public function kadasterEigendomsBerichtPostcodeV2($postcode, $huisnummer, $huisnummer_toevoeging)
    {
        return $this->_client->kadasterEigendomsBerichtPostcodeV2(['postcode' => $postcode, 'huisnummer' => $huisnummer, 'huisnummer_toevoeging' => $huisnummer_toevoeging]);
    }

    /*
        Returns a koopsommenoverzicht (in English: real estate transactions overview), which is a
        list of all transactions of the past five years in the given postcode range.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterKoopsommenOverzichtV2
    
        Parameters:
            string $postcode - Address postcode
            int $huisnummer - Address house number
            string $format - Filetype of the result (optional)
                    pdf  -- A pdf will be added to the result
                    none -- No document is added
    

        Returns:
            koopsommenoverzicht   - A <KoopsommenOverzichtV2> entry.
    */
    public function kadasterKoopsommenOverzichtV2($postcode, $huisnummer, $format)
    {
        return $this->_client->kadasterKoopsommenOverzichtV2(['postcode' => $postcode, 'huisnummer' => $huisnummer, 'format' => $format]);
    }

    /*
        Returns a 'Uittreksel Kadastrale Kaart' map in the specified format.
        The map displays parcel numbers and boundaries, building outlines, and house numbers.
        
        If one parcel is found, the "kadastrale_kaart" field of the <KadasterUittrekselKadastraleKaartResultaatV2>
        contains information about that specific parcel. If more parcels match, the "overzicht" field contains information about all parcels.
        
        For some valid parcels a Server.Data.NotFound error code (see <Error Handling::Error codes>) may be returned if the map isn't available.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterUittrekselKadastraleKaartPerceelV3
    
        Parameters:
            string $gemeentecode - Municipality code
            string $gemeentenaam - Municipality name. See <kadasterValueListGetMunicipalities> for possible values.
            string $sectie - Section code
            string $perceelnummer - Parcel number
            string $relatiecode - Indicates object relation type, set if object is part of another parcel. If relatiecode is specified, volgnummer should be specified as well. Allowed values: 'A', 'D', or empty.
            string $volgnummer - Object index number, set if object is part of another parcel
            string $format - Filetype of the map. The map will always be encoded in base 64. Supported formats:
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
    

        Returns:
            uittreksel   - A <KadasterUittrekselKadastraleKaartResultaatV2> entry.
    */
    public function kadasterUittrekselKadastraleKaartPerceelV3($gemeentecode, $gemeentenaam, $sectie, $perceelnummer, $relatiecode, $volgnummer, $format)
    {
        return $this->_client->kadasterUittrekselKadastraleKaartPerceelV3(['gemeentecode' => $gemeentecode, 'gemeentenaam' => $gemeentenaam, 'sectie' => $sectie, 'perceelnummer' => $perceelnummer, 'relatiecode' => $relatiecode, 'volgnummer' => $volgnummer, 'format' => $format]);
    }

    /*
        Returns a 'Uittreksel Kadastrale Kaart' map in the specified format.
        The map displays parcel numbers and boundaries, building outlines, and house numbers.
        
        Address information and parcels do not map 1 to 1. If one parcel is found, the "kadastrale_kaart" field of the <KadasterUittrekselKadastraleKaartResultaatV2>
        contains information about that specific parcel. If more parcels match, the "overzicht" field contains information about all parcels.
        
        For some valid parcels a Server.Data.NotFound error code (see <Error Handling::Error codes>) may be returned if the map isn't available.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterUittrekselKadastraleKaartPostcodeV3
    
        Parameters:
            string $postcode - Address postcode
            int $huisnummer - Address house number
            string $huisnummer_toevoeging - Address house number addition
            string $format - Filetype of the map. The map will always be encoded in base 64.
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
    

        Returns:
            uittreksel   - A <KadasterUittrekselKadastraleKaartResultaatV2> entry.
    */
    public function kadasterUittrekselKadastraleKaartPostcodeV3($postcode, $huisnummer, $huisnummer_toevoeging, $format)
    {
        return $this->_client->kadasterUittrekselKadastraleKaartPostcodeV3(['postcode' => $postcode, 'huisnummer' => $huisnummer, 'huisnummer_toevoeging' => $huisnummer_toevoeging, 'format' => $format]);
    }

    /*
        Returns a 'Kadata WMS De Kadastrale Kaart' map in the specified format. The map displays parcel boundaries, building outlines, and house numbers.
        
        Compared to <kadasterUittrekselKadastraleKaartPerceelV2> the maps returned by this method may be positioned and scaled less accurately
        for some parcels. You can override the automatically determined scale by providing a scale parameter.
        
        If one parcel is found, the "kadastrale_kaart" field of the <KadasterUittrekselKadastraleKaartResultaat>
        contains information about that one parcel. If more parcels match, the "overzicht" field contains information about all parcels.
        
        For some valid parcels a Server.Data.NotFound error code (see <Error Handling::Error codes>) may be returned if the map isn't available.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterKadastraleKaartPerceelV2
    
        Parameters:
            string $gemeentecode - Municipality code
            string $gemeentenaam - Municipality name. See <kadasterValueListGetMunicipalities> for possible values.
            string $sectie - Section code
            string $perceelnummer - Parcel number
            string $relatiecode - Indicates object relation type, set if object is part of another parcel. If relatiecode is specified, volgnummer should be specified as well. Allowed values: 'A', 'D', or empty.
            string $volgnummer - Object index number, set if object is part of another parcel
            string $format - Filetype of the map. The map will always be encoded in base 64. Supported formats:
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
            int $schaal - The scale used to generate the image. (optional) Available scales:
                    500 -- image scale 1:500
                    750 -- image scale 1:750
                    1000 -- image scale 1:1000
                    1500 -- image scale 1:1500
                    2000 -- image scale 1:2000
                    3000 -- image scale 1:3000
    

        Returns:
            kaart   - A <KadasterKadastraleKaartResultaatV2> entry.
    */
    public function kadasterKadastraleKaartPerceelV2($gemeentecode, $gemeentenaam, $sectie, $perceelnummer, $relatiecode, $volgnummer, $format, $schaal)
    {
        return $this->_client->kadasterKadastraleKaartPerceelV2(['gemeentecode' => $gemeentecode, 'gemeentenaam' => $gemeentenaam, 'sectie' => $sectie, 'perceelnummer' => $perceelnummer, 'relatiecode' => $relatiecode, 'volgnummer' => $volgnummer, 'format' => $format, 'schaal' => $schaal]);
    }

    /*
        Returns a 'Kadata WMS De Kadastrale Kaart' map in the specified format. The map displays parcel boundaries, building outlines, and house numbers.
        
        Compared to <kadasterUittrekselKadastraleKaartPostcodeV2> the maps returned by this method may be positioned and scaled less accurately
        for some parcels. You can override the automatically determined scale by providing a scale parameter.
        
        Address information and parcels do not map 1 to 1. If one parcel is found, the "kadastrale_kaart" field of the <KadasterUittrekselKadastraleKaartResultaat>
        contains information about that one parcel. If more parcels match, the "overzicht" field contains information about all parcels.
        
        For some valid parcels a Server.Data.NotFound error code (see <Error Handling::Error codes>) may be returned if the map isn't available.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterKadastraleKaartPostcodeV2
    
        Parameters:
            string $postcode - Address postcode
            int $huisnummer - Address house number
            string $huisnummer_toevoeging - Address house number addition
            string $format - Filetype of the map. The map will always be encoded in base 64.
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
            int $schaal - The scale used to generate the image. (optional) Available scales:
                    500 -- image scale 1:500
                    750 -- image scale 1:750
                    1000 -- image scale 1:1000
                    1500 -- image scale 1:1500
                    2000 -- image scale 1:2000
                    3000 -- image scale 1:3000
    

        Returns:
            kaart   - A <KadasterKadastraleKaartResultaatV2> entry.
    */
    public function kadasterKadastraleKaartPostcodeV2($postcode, $huisnummer, $huisnummer_toevoeging, $format, $schaal)
    {
        return $this->_client->kadasterKadastraleKaartPostcodeV2(['postcode' => $postcode, 'huisnummer' => $huisnummer, 'huisnummer_toevoeging' => $huisnummer_toevoeging, 'format' => $format, 'schaal' => $schaal]);
    }

    /*
        Find a 'Hypothecair bericht' by postcode and house number.
        
        This method differs from <kadasterHypothecairBerichtPostcodeV2> in the way that this method can return
        overviews containing persons that are registered as protected. For these persons a alert called "melding" is added.
        The old <kadasterHypothecairBerichtPostcodeV2> method returns a *Server.Data.NotFound.Kadaster.NotDeliverable* error
        for overviews containing protected persons in these cases.
        
        The overview containing the alert can be tested by setting format parameter to "test".
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterHypothecairBerichtPostcodeV3
    
        Parameters:
            string $postcode - Address postcode
            int $huisnummer - Address house number
            string $huisnummer_toevoeging - Address house number addition. Note: this parameter currently has no effect
            string $format - Filetype of the result. The result will always be encoded in base 64. If an image format is requested a conversion is performed on our servers, which might
                    cause the response to be delayed for large documents. We recommend using a longer timeout setting for such requests. Supported formats:
                    none -- No document will be returned.
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    test -- A predefined test response is returned containing the overview alert called "melding".
    

        Returns:
            <kadasterHypothecairBerichtResultaatV2>
    */
    public function kadasterHypothecairBerichtPostcodeV3($postcode, $huisnummer, $huisnummer_toevoeging, $format)
    {
        return $this->_client->kadasterHypothecairBerichtPostcodeV3(['postcode' => $postcode, 'huisnummer' => $huisnummer, 'huisnummer_toevoeging' => $huisnummer_toevoeging, 'format' => $format]);
    }

    /*
        Find a 'Hypothecair bericht' by parcel details.
        
        This method differs from <kadasterHypothecairBerichtPerceelV2> in the way that this method can return
        overviews containing persons that are registered as protected. For these persons a alert called "melding" is added.
        The old <kadasterHypothecairBerichtPerceelV2> method returns a *Server.Data.NotFound.Kadaster.NotDeliverable* error
        for overviews containing protected persons in these cases.
        
        To test a "hypothecair bericht" that can't be delivered a test case is added. This test can be used to check if the
        Server.Data.NotFound.Kadaster.NotDeliverable* error implementation is correct within your application. For this the format
        "test" is added.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterHypothecairBerichtPerceelV3
    
        Parameters:
            string $gemeentecode - Municipality code
            string $gemeentenaam - Municipality name. See <kadasterValueListGetMunicipalities> for possible values.
            string $sectie - Section code
            string $perceelnummer - Parcel number
            string $relatiecode - Indicates object relation type, set if object is part of another parcel. If relatiecode is specified, volgnummer should be specified as well. Allowed values: 'A', 'D', or empty.
            string $volgnummer - Object index number, set if object is part of another parcel
            string $format - Filetype of the result. The result will always be encoded in base 64. If an image format is requested a conversion is performed on our servers, which might
                    cause the response to be delayed for large documents. We recommend using a longer timeout setting for such requests. Supported formats:
                    none -- No document will be returned.
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    test -- Test a document that can't be devivered
    

        Returns:
            <kadasterHypothecairBerichtResultaatV2>
    */
    public function kadasterHypothecairBerichtPerceelV3($gemeentecode, $gemeentenaam, $sectie, $perceelnummer, $relatiecode, $volgnummer, $format)
    {
        return $this->_client->kadasterHypothecairBerichtPerceelV3(['gemeentecode' => $gemeentecode, 'gemeentenaam' => $gemeentenaam, 'sectie' => $sectie, 'perceelnummer' => $perceelnummer, 'relatiecode' => $relatiecode, 'volgnummer' => $volgnummer, 'format' => $format]);
    }

    /*
        Find a 'brondocument', a document which is the basis for an ascription.
        See <Example documents> for an example document.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterBronDocument
    
        Parameters:
            string $aanduiding_soort_register - Identifies the type of register in which the document was registered.
                    Supported values:
                    3 -- Register of mortgage documents (dutch: hypotheekakte)
                    4 -- Register of transport documents (dutch: transportakte)
            string $deel - Unique identifier for a group of documents within a register of a Kadaster establishment. Defined as a string
                    because alphanumeric values are used in the past, maximum length 5 characters.
            string $nummer - Alphanumeric number used to identify a document. Note that a number does not relate to a single revision of the document,
                    since numbers may be reused if a change is issued on time.
            string $reeks - Identifier for the (former) establishment of the Kadaster where the Stuk was originally registered.
                    This parameter is required for requests where deel is less than 50000, and may be left empty if
                    deel is 50000 or higher. Use <kadasterValueListGetRanges> to get a full list of possible "reeks" values.
            string $format - Filetype of the result. The result will always be encoded in base 64. If an image format is requested a conversion is performed on our servers, which might
                    cause the response to be delayed for large documents. We recommend using a longer timeout setting for such requests. Supported formats:
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
    

        Returns:
            bron_document - A <KadasterBronDocument> entry.
    */
    public function kadasterBronDocument($aanduiding_soort_register, $deel, $nummer, $reeks, $format)
    {
        return $this->_client->kadasterBronDocument(['aanduiding_soort_register' => $aanduiding_soort_register, 'deel' => $deel, 'nummer' => $nummer, 'reeks' => $reeks, 'format' => $format]);
    }

    /*
        
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterValueListGetRanges
    
        Parameters:
            None
    

        Returns:
            out   - A <KadasterValueList> entry.
    */
    public function kadasterValueListGetRanges()
    {
        return $this->_client->kadasterValueListGetRanges([]);
    }

    /*
        
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterValueListGetMunicipalities
    
        Parameters:
            None
    

        Returns:
            out   - A <KadasterValueList> entry.
    */
    public function kadasterValueListGetMunicipalities()
    {
        return $this->_client->kadasterValueListGetMunicipalities([]);
    }

    /*
        Notice:
        This method is deprecated, use <kadasterUittrekselKadastraleKaartPerceelV2> instead
        
        For some valid parcels a Server.Data.NotFound error code (see <Error Handling::Error codes>) may be returned if the map isn't available.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterUittrekselKadastraleKaartPerceel
    
        Parameters:
            string $gemeentecode - Municipality code
            string $gemeentenaam - Municipality name
            string $sectie - Section code
            string $perceelnummer - Parcel number
            string $relatiecode - Indicates object relation type, set if object is part of another parcel. If relatiecode is specified, volgnummer should be specified as well. Allowed values: 'A', 'D', or empty.
            string $volgnummer - Object index number, set if object is part of another parcel
            string $format - Filetype of the map. The map will always be encoded in base 64. Supported formats:
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
    

        Returns:
            uittreksel   - A <UittrekselKadastraleKaart> entry.
    */
    public function kadasterUittrekselKadastraleKaartPerceel($gemeentecode, $gemeentenaam, $sectie, $perceelnummer, $relatiecode, $volgnummer, $format)
    {
        return $this->_client->kadasterUittrekselKadastraleKaartPerceel(['gemeentecode' => $gemeentecode, 'gemeentenaam' => $gemeentenaam, 'sectie' => $sectie, 'perceelnummer' => $perceelnummer, 'relatiecode' => $relatiecode, 'volgnummer' => $volgnummer, 'format' => $format]);
    }

    /*
        Notice:
        This method is deprecated, use <kadasterUittrekselKadastraleKaartPostcodeV2> instead
        
        For some valid parcels a Server.Data.NotFound error code (see <Error Handling::Error codes>) may be returned if the map isn't available.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterUittrekselKadastraleKaartPostcode
    
        Parameters:
            string $postcode - Address postcode
            int $huisnummer - Address house number
            string $huisnummer_toevoeging - Address house number addition
            string $format - Filetype of the map. The map will always be encoded in base 64. Supported formats:
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
    

        Returns:
            uittreksel   - A <UittrekselKadastraleKaart> entry.
    */
    public function kadasterUittrekselKadastraleKaartPostcode($postcode, $huisnummer, $huisnummer_toevoeging, $format)
    {
        return $this->_client->kadasterUittrekselKadastraleKaartPostcode(['postcode' => $postcode, 'huisnummer' => $huisnummer, 'huisnummer_toevoeging' => $huisnummer_toevoeging, 'format' => $format]);
    }

    /*
        Notice:
        This method is deprecated, use <kadasterHypothecairBerichtPostcodeV2> instead
        
        Find a 'Hypothecair bericht' by postcode and house number.
        
        Address information and parcels do not map 1 to 1. If one parcel is found, the "hypothecairbericht" field of the <kadasterHypothecairBerichtResultaat>
        contains information about that specific parcel. If more parcels match, the "overzicht" field contains information about all parcels.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterHypothecairBerichtPostcode
    
        Parameters:
            string $postcode - Address postcode
            int $huisnummer - Address house number
            string $huisnummer_toevoeging - Address house number addition. Note: this parameter currently has no effect
            string $format - Filetype of the result. The result will always be encoded in base 64. If an image format is requested a conversion is performed on our servers, which might
                    cause the response to be delayed for large documents. We recommend using a longer timeout setting for such requests. Supported formats:
                    none -- No document will be returned.
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
    

        Returns:
            <kadasterHypothecairBerichtResultaat>
    */
    public function kadasterHypothecairBerichtPostcode($postcode, $huisnummer, $huisnummer_toevoeging, $format)
    {
        return $this->_client->kadasterHypothecairBerichtPostcode(['postcode' => $postcode, 'huisnummer' => $huisnummer, 'huisnummer_toevoeging' => $huisnummer_toevoeging, 'format' => $format]);
    }

    /*
        Notice:
        This method is deprecated, use <kadasterHypothecairBerichtPerceelV2> instead
        
        Find a 'Hypothecair bericht' by parcel details.
        
        If one parcel is found, the "hypothecairbericht" field of the <kadasterHypothecairBerichtResultaat>
        contains information about that specific parcel. If more parcels match, the "overzicht" field contains
        information about all the parcels the requested parcel has been divided in, or transferred into.
        
        Sectie, perceelnummer and the code or name of the municipality are required.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterHypothecairBerichtPerceel
    
        Parameters:
            string $gemeentecode - Municipality code
            string $gemeentenaam - Municipality name
            string $sectie - Section code
            string $perceelnummer - Parcel number
            string $relatiecode - Indicates object relation type, set if object is part of another parcel. If relatiecode is specified, volgnummer should be specified as well. Allowed values: 'A', 'D', or empty.
            string $volgnummer - Object index number, set if object is part of another parcel
            string $format - Filetype of the result. The result will always be encoded in base 64. If an image format is requested a conversion is performed on our servers, which might
                    cause the response to be delayed for large documents. We recommend using a longer timeout setting for such requests. Supported formats:
                    none -- No document will be returned.
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
    

        Returns:
            <kadasterHypothecairBerichtResultaat>
    */
    public function kadasterHypothecairBerichtPerceel($gemeentecode, $gemeentenaam, $sectie, $perceelnummer, $relatiecode, $volgnummer, $format)
    {
        return $this->_client->kadasterHypothecairBerichtPerceel(['gemeentecode' => $gemeentecode, 'gemeentenaam' => $gemeentenaam, 'sectie' => $sectie, 'perceelnummer' => $perceelnummer, 'relatiecode' => $relatiecode, 'volgnummer' => $volgnummer, 'format' => $format]);
    }

    /*
        Notice:
        This method is deprecated, use <kadasterEigendomsBerichtPerceelV2> instead
        
        Find a 'Eigendomsbericht' by parcel details.
        Returns the result as a <BerichtObjectResultaat>.
        In addition to the structured result, a file in the PDF format is returned.
        If the input matches more than one parcel, a list of the parcels found is returned instead.
        Sectie, perceelnummer and the code or name of the municipality are required.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterEigendomsBerichtPerceel
    
        Parameters:
            string $gemeentecode - Municipality code
            string $gemeentenaam - Municipality name
            string $sectie - Section code
            string $perceelnummer - Parcel number
            string $relatiecode - Indicates object relation type, set if object is part of another parcel. If relatiecode is specified, volgnummer should be specified as well. Allowed values: 'A', 'D', or empty.
            string $volgnummer - Object index number, set if object is part of another parcel
    

        Returns:
            eigendomsberichtresultaat - A <BerichtObjectResultaat> entry.
    */
    public function kadasterEigendomsBerichtPerceel($gemeentecode, $gemeentenaam, $sectie, $perceelnummer, $relatiecode, $volgnummer)
    {
        return $this->_client->kadasterEigendomsBerichtPerceel(['gemeentecode' => $gemeentecode, 'gemeentenaam' => $gemeentenaam, 'sectie' => $sectie, 'perceelnummer' => $perceelnummer, 'relatiecode' => $relatiecode, 'volgnummer' => $volgnummer]);
    }

    /*
        Notice:
        This method is deprecated, use <kadasterEigendomsBerichtPostcodeV2> instead
        
        Find a 'Eigendomsbericht' by postcode and house number.
        Returns the result as a <BerichtObjectResultaat>.
        In addition to the structured result, a file in the PDF format is returned.
        If the input matches more than one parcel, a list of the parcels found is returned instead.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterEigendomsBerichtPostcode
    
        Parameters:
            string $postcode - Address postcode
            int $huisnummer - Address house number
            string $huisnummer_toevoeging - Address house number addition
    

        Returns:
            eigendomsberichtresultaat - A <BerichtObjectResultaat> entry.
    */
    public function kadasterEigendomsBerichtPostcode($postcode, $huisnummer, $huisnummer_toevoeging)
    {
        return $this->_client->kadasterEigendomsBerichtPostcode(['postcode' => $postcode, 'huisnummer' => $huisnummer, 'huisnummer_toevoeging' => $huisnummer_toevoeging]);
    }

    /*
        Notice:
        This method is deprecated, use <kadasterUittrekselKadastraleKaartPerceelV3> instead
        
        Returns a 'Uittreksel Kadastrale Kaart' map in the specified format.
        The map displays parcel numbers and boundaries, building outlines, and house numbers.
        
        If one parcel is found, the "kadastrale_kaart" field of the <KadasterUittrekselKadastraleKaartResultaat>
        contains information about that specific parcel. If more parcels match, the "overzicht" field contains information about all parcels.
        
        For some valid parcels a Server.Data.NotFound error code (see <Error Handling::Error codes>) may be returned if the map isn't available.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterUittrekselKadastraleKaartPerceelV2
    
        Parameters:
            string $gemeentecode - Municipality code
            string $gemeentenaam - Municipality name
            string $sectie - Section code
            string $perceelnummer - Parcel number
            string $relatiecode - Indicates object relation type, set if object is part of another parcel. If relatiecode is specified, volgnummer should be specified as well. Allowed values: 'A', 'D', or empty.
            string $volgnummer - Object index number, set if object is part of another parcel
            string $format - Filetype of the map. The map will always be encoded in base 64. Supported formats:
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
    

        Returns:
            uittreksel   - A <KadasterUittrekselKadastraleKaartResultaat> entry.
    */
    public function kadasterUittrekselKadastraleKaartPerceelV2($gemeentecode, $gemeentenaam, $sectie, $perceelnummer, $relatiecode, $volgnummer, $format)
    {
        return $this->_client->kadasterUittrekselKadastraleKaartPerceelV2(['gemeentecode' => $gemeentecode, 'gemeentenaam' => $gemeentenaam, 'sectie' => $sectie, 'perceelnummer' => $perceelnummer, 'relatiecode' => $relatiecode, 'volgnummer' => $volgnummer, 'format' => $format]);
    }

    /*
        Notice:
        This method is deprecated, use <kadasterUittrekselKadastraleKaartPostcodeV3> instead
        
        Returns a 'Uittreksel Kadastrale Kaart' map in the specified format.
        The map displays parcel numbers and boundaries, building outlines, and house numbers.
        
        Address information and parcels do not map 1 to 1. If one parcel is found, the "kadastrale_kaart" field of the <KadasterUittrekselKadastraleKaartResultaat>
        contains information about that specific parcel. If more parcels match, the "overzicht" field contains information about all parcels.
        
        For some valid parcels a Server.Data.NotFound error code (see <Error Handling::Error codes>) may be returned if the map isn't available.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterUittrekselKadastraleKaartPostcodeV2
    
        Parameters:
            string $postcode - Address postcode
            int $huisnummer - Address house number
            string $huisnummer_toevoeging - Address house number addition
            string $format - Filetype of the map. The map will always be encoded in base 64.
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
    

        Returns:
            uittreksel   - A <KadasterUittrekselKadastraleKaartResultaat> entry.
    */
    public function kadasterUittrekselKadastraleKaartPostcodeV2($postcode, $huisnummer, $huisnummer_toevoeging, $format)
    {
        return $this->_client->kadasterUittrekselKadastraleKaartPostcodeV2(['postcode' => $postcode, 'huisnummer' => $huisnummer, 'huisnummer_toevoeging' => $huisnummer_toevoeging, 'format' => $format]);
    }

    /*
        Notice:
        This method is deprecated, use <kadasterKadastraleKaartPerceelV2> instead
        
        Returns a 'Kadata WMS De Kadastrale Kaart' map in the specified format. The map displays parcel boundaries, building outlines, and house numbers.
        
        Compared to <kadasterUittrekselKadastraleKaartPerceelV2> the maps returned by this method may be positioned and scaled less accurately
        for some parcels. You can override the automatically determined scale by providing a scale parameter.
        
        If one parcel is found, the "kadastrale_kaart" field of the <KadasterUittrekselKadastraleKaartResultaat>
        contains information about that one parcel. If more parcels match, the "overzicht" field contains information about all parcels.
        
        For some valid parcels a Server.Data.NotFound error code (see <Error Handling::Error codes>) may be returned if the map isn't available.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterKadastraleKaartPerceel
    
        Parameters:
            string $gemeentecode - Municipality code
            string $gemeentenaam - Municipality name
            string $sectie - Section code
            string $perceelnummer - Parcel number
            string $relatiecode - Indicates object relation type, set if object is part of another parcel. If relatiecode is specified, volgnummer should be specified as well. Allowed values: 'A', 'D', or empty.
            string $volgnummer - Object index number, set if object is part of another parcel
            string $format - Filetype of the map. The map will always be encoded in base 64. Supported formats:
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
            int $schaal - The scale used to generate the image. (optional) Available scales:
                    500 -- image scale 1:500
                    750 -- image scale 1:750
                    1000 -- image scale 1:1000
                    1500 -- image scale 1:1500
                    2000 -- image scale 1:2000
                    3000 -- image scale 1:3000
    

        Returns:
            kaart   - A <KadasterKadastraleKaartResultaat> entry.
    */
    public function kadasterKadastraleKaartPerceel($gemeentecode, $gemeentenaam, $sectie, $perceelnummer, $relatiecode, $volgnummer, $format, $schaal)
    {
        return $this->_client->kadasterKadastraleKaartPerceel(['gemeentecode' => $gemeentecode, 'gemeentenaam' => $gemeentenaam, 'sectie' => $sectie, 'perceelnummer' => $perceelnummer, 'relatiecode' => $relatiecode, 'volgnummer' => $volgnummer, 'format' => $format, 'schaal' => $schaal]);
    }

    /*
        Notice:
        This method is deprecated, use <kadasterKadastraleKaartPostcodeV2> instead
        
        Returns a 'Kadata WMS De Kadastrale Kaart' map in the specified format. The map displays parcel boundaries, building outlines, and house numbers.
        
        Compared to <kadasterUittrekselKadastraleKaartPostcodeV2> the maps returned by this method may be positioned and scaled less accurately
        for some parcels. You can override the automatically determined scale by providing a scale parameter.
        
        Address information and parcels do not map 1 to 1. If one parcel is found, the "kadastrale_kaart" field of the <KadasterUittrekselKadastraleKaartResultaat>
        contains information about that one parcel. If more parcels match, the "overzicht" field contains information about all parcels.
        
        For some valid parcels a Server.Data.NotFound error code (see <Error Handling::Error codes>) may be returned if the map isn't available.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterKadastraleKaartPostcode
    
        Parameters:
            string $postcode - Address postcode
            int $huisnummer - Address house number
            string $huisnummer_toevoeging - Address house number addition
            string $format - Filetype of the map. The map will always be encoded in base 64.
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
            int $schaal - The scale used to generate the image. (optional) Available scales:
                    500 -- image scale 1:500
                    750 -- image scale 1:750
                    1000 -- image scale 1:1000
                    1500 -- image scale 1:1500
                    2000 -- image scale 1:2000
                    3000 -- image scale 1:3000
    

        Returns:
            kaart   - A <KadasterKadastraleKaartResultaat> entry.
    */
    public function kadasterKadastraleKaartPostcode($postcode, $huisnummer, $huisnummer_toevoeging, $format, $schaal)
    {
        return $this->_client->kadasterKadastraleKaartPostcode(['postcode' => $postcode, 'huisnummer' => $huisnummer, 'huisnummer_toevoeging' => $huisnummer_toevoeging, 'format' => $format, 'schaal' => $schaal]);
    }

    /*
        Notice:
        This method is deprecated, use <kadasterHypothecairBerichtPostcodeV3> instead
        
        Find a 'Hypothecair bericht' by postcode and house number.
        
        Address information and parcels do not map 1 to 1. If one parcel is found, the "hypothecairbericht" field of the <kadasterHypothecairBerichtResultaat>
        contains information about that specific parcel. If more parcels match, the "overzicht" field contains information about all parcels.
        
        This method differs <kadasterHypothecairBerichtPostcode> from in the way that <kadasterStukdeel> information is returned,
        see <kadasterStukdeel> for more information.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterHypothecairBerichtPostcodeV2
    
        Parameters:
            string $postcode - Address postcode
            int $huisnummer - Address house number
            string $huisnummer_toevoeging - Address house number addition. Note: this parameter currently has no effect
            string $format - Filetype of the result. The result will always be encoded in base 64. If an image format is requested a conversion is performed on our servers, which might
                    cause the response to be delayed for large documents. We recommend using a longer timeout setting for such requests. Supported formats:
                    none -- No document will be returned.
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
    

        Returns:
            <kadasterHypothecairBerichtResultaat>
    */
    public function kadasterHypothecairBerichtPostcodeV2($postcode, $huisnummer, $huisnummer_toevoeging, $format)
    {
        return $this->_client->kadasterHypothecairBerichtPostcodeV2(['postcode' => $postcode, 'huisnummer' => $huisnummer, 'huisnummer_toevoeging' => $huisnummer_toevoeging, 'format' => $format]);
    }

    /*
        Notice:
        This method is deprecated, use <kadasterHypothecairBerichtPerceelV3> instead
        
        Find a 'Hypothecair bericht' by parcel details.
        
        If one parcel is found, the "hypothecairbericht" field of the <kadasterHypothecairBerichtResultaat>
        contains information about that specific parcel. If more parcels match, the "overzicht" field contains
        information about all the parcels the requested parcel has been divided in, or transferred into.
        
        Sectie, perceelnummer and the code or name of the municipality are required.
        
        This method differs <kadasterHypothecairBerichtPerceel> from in the way that <kadasterStukdeel> information is returned,
        see <kadasterStukdeel> for more information.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterHypothecairBerichtPerceelV2
    
        Parameters:
            string $gemeentecode - Municipality code
            string $gemeentenaam - Municipality name
            string $sectie - Section code
            string $perceelnummer - Parcel number
            string $relatiecode - Indicates object relation type, set if object is part of another parcel. If relatiecode is specified, volgnummer should be specified as well. Allowed values: 'A', 'D', or empty.
            string $volgnummer - Object index number, set if object is part of another parcel
            string $format - Filetype of the result. The result will always be encoded in base 64. If an image format is requested a conversion is performed on our servers, which might
                    cause the response to be delayed for large documents. We recommend using a longer timeout setting for such requests. Supported formats:
                    none -- No document will be returned.
                    pdf -- Only a PDF document will be returned.
                    png_16 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 132 by 187 pixels.
                    png_144 -- A PDF file, and one PNG image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
                    gif_144 -- A PDF file, and one GIF image for every page will be returned. Each image is approximately 1190 by 1684 pixels.
    

        Returns:
            <kadasterHypothecairBerichtResultaat>
    */
    public function kadasterHypothecairBerichtPerceelV2($gemeentecode, $gemeentenaam, $sectie, $perceelnummer, $relatiecode, $volgnummer, $format)
    {
        return $this->_client->kadasterHypothecairBerichtPerceelV2(['gemeentecode' => $gemeentecode, 'gemeentenaam' => $gemeentenaam, 'sectie' => $sectie, 'perceelnummer' => $perceelnummer, 'relatiecode' => $relatiecode, 'volgnummer' => $volgnummer, 'format' => $format]);
    }

    /*
        Notice:
        This method is deprecated, use <kadasterKoopsommenOverzichtV2> instead
        
        Returns a koopsommenoverzicht (in English: real estate transactions overview), which is a
        list of all transactions of the past five years in the given postcode range.
    
        http://webview.webservices.nl/documentation/files/service_kadaster-class-php.html#Kadaster.kadasterKoopsommenOverzicht
    
        Parameters:
            string $postcode - Address postcode
            int $huisnummer - Address house number
    

        Returns:
            koopsommenoverzicht   - A <KoopsommenOverzicht> entry.
    */
    public function kadasterKoopsommenOverzicht($postcode, $huisnummer)
    {
        return $this->_client->kadasterKoopsommenOverzicht(['postcode' => $postcode, 'huisnummer' => $huisnummer]);
    }

    public function kvkGetDossier($dossier_number, $establishment_number)
    {
        return $this->_client->kvkGetDossier(['dossier_number' => $dossier_number, 'establishment_number' => $establishment_number]);
    }

    public function kvkSearchDossierNumber($dossier_number, $establishment_number, $rsin_number, $page)
    {
        return $this->_client->kvkSearchDossierNumber(['dossier_number' => $dossier_number, 'establishment_number' => $establishment_number, 'rsin_number' => $rsin_number, 'page' => $page]);
    }

    public function kvkSearchParameters($trade_name, $city, $street, $postcode, $house_number, $house_number_addition, $telephone_number, $domain_name, $strict_search, $page)
    {
        return $this->_client->kvkSearchParameters(['trade_name' => $trade_name, 'city' => $city, 'street' => $street, 'postcode' => $postcode, 'house_number' => $house_number, 'house_number_addition' => $house_number_addition, 'telephone_number' => $telephone_number, 'domain_name' => $domain_name, 'strict_search' => $strict_search, 'page' => $page]);
    }

    public function kvkSearchPostcode($postcode, $house_number, $house_number_addition, $page)
    {
        return $this->_client->kvkSearchPostcode(['postcode' => $postcode, 'house_number' => $house_number, 'house_number_addition' => $house_number_addition, 'page' => $page]);
    }

    public function kvkSearchSelection($city, $postcode, $sbi, $primary_sbi_only, $legal_form, $employees_min, $employees_max, $economically_active, $financial_status, $changed_since, $new_since, $page)
    {
        return $this->_client->kvkSearchSelection(['city' => $city, 'postcode' => $postcode, 'sbi' => $sbi, 'primary_sbi_only' => $primary_sbi_only, 'legal_form' => $legal_form, 'employees_min' => $employees_min, 'employees_max' => $employees_max, 'economically_active' => $economically_active, 'financial_status' => $financial_status, 'changed_since' => $changed_since, 'new_since' => $new_since, 'page' => $page]);
    }

    public function kvkGetExtractDocument($dossier_number, $allow_caching)
    {
        return $this->_client->kvkGetExtractDocument(['dossier_number' => $dossier_number, 'allow_caching' => $allow_caching]);
    }

    public function kvkUpdateCheckDossier($dossier_number, $establishment_number, $update_types)
    {
        return $this->_client->kvkUpdateCheckDossier(['dossier_number' => $dossier_number, 'establishment_number' => $establishment_number, 'update_types' => $update_types]);
    }

    public function kvkUpdateGetChangedDossiers($changed_since, $update_types, $page)
    {
        return $this->_client->kvkUpdateGetChangedDossiers(['changed_since' => $changed_since, 'update_types' => $update_types, 'page' => $page]);
    }

    public function kvkUpdateGetDossiers($update_types, $page)
    {
        return $this->_client->kvkUpdateGetDossiers(['update_types' => $update_types, 'page' => $page]);
    }

    public function kvkUpdateAddDossier($dossier_number, $establishment_number)
    {
        return $this->_client->kvkUpdateAddDossier(['dossier_number' => $dossier_number, 'establishment_number' => $establishment_number]);
    }

    public function kvkUpdateRemoveDossier($dossier_number, $establishment_number)
    {
        return $this->_client->kvkUpdateRemoveDossier(['dossier_number' => $dossier_number, 'establishment_number' => $establishment_number]);
    }

    /*
        Returns a map in JPG or PNG format showing the location of the given postcode.
    
        http://webview.webservices.nl/documentation/files/service_map-class-php.html#Map.mapViewPostcodeV2
    
        Parameters:
            string $postcode - The postcode
            string $format - Imageformat, PNG (default) or JPG
            int $width - Width in pixels [1 - 2048]
            int $height - Height in pixels [1 - 2048]
            float $zoom - Scale in meters per pixel. See: <Zoom>
    

        Returns:
            image - A JPEG or PNG image, base64 encoded.
    */
    public function mapViewPostcodeV2($postcode, $format, $width, $height, $zoom)
    {
        return $this->_client->mapViewPostcodeV2(['postcode' => $postcode, 'format' => $format, 'width' => $width, 'height' => $height, 'zoom' => $zoom]);
    }

    /*
        Returns a map in PNG or JPG format centered on the latlon coordinate.
        The extra_latlon parameter can be used to specify additional locations,
        the map is not centered or zoomed to automatically enclose these
        locations.
    
        http://webview.webservices.nl/documentation/files/service_map-class-php.html#Map.mapViewLatLon
    
        Parameters:
            float $center_lat - The latitude component of the coordinate.
            float $center_lon - The longitude component of the coordinate.
            LatLonCoordinatesArray $extra_latlon - Additional Coordinates, an <Patterns::{Type}Array> of type <LatLonCoordinates>
            string $format - Imageformat, PNG (default) or JPG
            int $width - Width in pixels, domain [1 - 2048]
            int $height - Height in pixels, domain [1 - 2048]
            float $zoom - Scale in meters per pixel. See: <Zoom>
    

        Returns:
            image - A JPEG or PNG image, base64 encoded.
    */
    public function mapViewLatLon($center_lat, $center_lon, $extra_latlon, $format, $width, $height, $zoom)
    {
        return $this->_client->mapViewLatLon(['center_lat' => $center_lat, 'center_lon' => $center_lon, 'extra_latlon' => $extra_latlon, 'format' => $format, 'width' => $width, 'height' => $height, 'zoom' => $zoom]);
    }

    /*
        Returns a map in PNG or JPG format centered on the xy RD coordinate.
        The extra_xy parameter can be used to specify additional locations,
        the map is not centered or zoomed to automatically enclose these
        locations.
    
        http://webview.webservices.nl/documentation/files/service_map-class-php.html#Map.mapViewRD
    
        Parameters:
            int $center_x - The RD X component of the coordinate.
            int $center_y - The RD Y component of the coordinate.
            RDCoordinatesArray $extra_xy - Additional RDCoordinates, an <Patterns::{Type}Array> of type <RDCoordinates>
            string $format - Imageformat, PNG (default) or JPG
            int $width - Width in pixels, domain [1 - 2048]
            int $height - Height in pixels, domain [1 - 2048]
            float $zoom - Scale in meters per pixel. See: <Zoom>
    

        Returns:
            image - A JPEG or PNG image, base64 encoded.
    */
    public function mapViewRD($center_x, $center_y, $extra_xy, $format, $width, $height, $zoom)
    {
        return $this->_client->mapViewRD(['center_x' => $center_x, 'center_y' => $center_y, 'extra_xy' => $extra_xy, 'format' => $format, 'width' => $width, 'height' => $height, 'zoom' => $zoom]);
    }

    /*
        Returns a map centered on the lat/lon coordinate.
    
        http://webview.webservices.nl/documentation/files/service_map-class-php.html#Map.mapViewInternationalLatLon
    
        Parameters:
            float $latitude - The latitude component of the coordinate.
            float $longitude - The longitude component of the coordinate.
            string $format - Image format. Supported formats: 'JPG', 'PNG'
            int $width - Width in pixels, domain [1-2048]
            int $height - Height in pixels, domain [1-2048]
            float $zoom - Scale in meters per pixel. See: <Zoom>
    

        Returns:
            image - A base64 encoded image.
    */
    public function mapViewInternationalLatLon($latitude, $longitude, $format, $width, $height, $zoom)
    {
        return $this->_client->mapViewInternationalLatLon(['latitude' => $latitude, 'longitude' => $longitude, 'format' => $format, 'width' => $width, 'height' => $height, 'zoom' => $zoom]);
    }

    /*
        Returns a value estimate for the real estate at the specified address.
        The required parameters are: postcode, houseno and testing_date.
        
        The remaining parameters are retrieved from the Kadaster if available.
        If those parameters are specified in the request they override any Kadaster data,
        and will be used in the calculation of the value estimate.
        
        If no value estimate can be determined the response will be an error with error code (see <Error Handling::Error codes>) 'Server.Data.NotFound.Nbwo.EstimateUnavailable'.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $postcode - Postcode
            int $huisnummer - House number
            string $huisnummer_toevoeging - House number addition, may be left empty
            string $prijspeil_datum - Date for which the value should be determined, in the format yyyy-mm-dd
            string $woningtype - The type of house, may be empty:
                    A -- Appartment
                    H -- Corner house (Hoekwoning)
                    K -- Semi detached house (Twee onder een kap)
                    N -- Not a house
                    O -- Unknown type of house
                    T -- Townhouse (Tussingwoning)
                    V -- Detached house (Vrijstaande woning)
            int $inhoud - Volume in cubic meters, may be empty (0)
            int $grootte - Surface area of the parcel in square meters, may be empty (0)
    

        Returns:
            out		- A <NbwoWaarde> entry
    */
    public function nbwoEstimateValue($postcode, $huisnummer, $huisnummer_toevoeging, $prijspeil_datum, $woningtype, $inhoud, $grootte)
    {
        return $this->_client->nbwoEstimateValue(['postcode' => $postcode, 'huisnummer' => $huisnummer, 'huisnummer_toevoeging' => $huisnummer_toevoeging, 'prijspeil_datum' => $prijspeil_datum, 'woningtype' => $woningtype, 'inhoud' => $inhoud, 'grootte' => $grootte]);
    }

    /*
        Returns a score indicating creditworthiness for a Dutch person, address and postcode area.
        The given parameters are used to search for a person.
        
        The following fields are tried in the order listed, until a matching person is found:
        1 - initials, name, postcode, house number
        2 - initials, name, birth date
        3 - postcode, house number, birth date
        4 - account number, birth date
        5 - phone number, birth date
        6 - mobile number, birth date
        7 - email, birth date
        
        For instance, if initials, postcode, house number and birth date are specified and a match is found
        on the fields listed under 1, birth date will be ignored.
        
        Scores for address and postcode are determined independent of the person details.
        
        Search fields are case-insensitive. Non-ASCII characters are mapped to the
        corresponding character without diacritical mark (e.g. an accented e is mapped to an 'e').
    
        http://webview.webservices.nl/documentation/files/service_riskcheck-class-php.html#RiskCheck.riskCheckPerson
    
        Parameters:
            string $gender - Gender of the person. M or F, may be empty.
            string $initials - The initials, mandatory.
            string $prefix - The surname prefix, like "van" or "de", may be empty.
            string $last_name - The last name of the person, mandatory.
            string $birth_date - Birth date in the format yyyy-mm-dd, may be empty.
            string $street - Street part of the address, may be empty.
            int $house_number - House number, mandatory.
            string $house_number_addition - Extension part of the house number, may be empty.
            string $postcode - Dutch postcode in the format 1234AB, mandatory.
            string $city - City, may be empty.
            string $account_number - Bank account number, only numeric characters, may be empty.
            string $phone_number - Home phone number, only numeric characters (e.g. 0201234567), may be empty.
            string $mobile_number - Mobile phone number, only numeric characters (e.g. 0612345678), may be empty.
            string $email - Email address, may be empty.
            string $testing_date - Date for which the credit score should be determined, in the format yyyy-mm-dd, mandatory.
    

        Returns:
            out		- A <RiskResult> entry
    */
    public function riskCheckPerson($gender, $initials, $prefix, $last_name, $birth_date, $street, $house_number, $house_number_addition, $postcode, $city, $account_number, $phone_number, $mobile_number, $email, $testing_date)
    {
        return $this->_client->riskCheckPerson(['gender' => $gender, 'initials' => $initials, 'prefix' => $prefix, 'last_name' => $last_name, 'birth_date' => $birth_date, 'street' => $street, 'house_number' => $house_number, 'house_number_addition' => $house_number_addition, 'postcode' => $postcode, 'city' => $city, 'account_number' => $account_number, 'phone_number' => $phone_number, 'mobile_number' => $mobile_number, 'email' => $email, 'testing_date' => $testing_date]);
    }

    /*
        Retrieve information about a persons creditworthiness based on his/her business ownerships and insolvency registrations.
    
        http://webview.webservices.nl/documentation/files/service_riskcheck-class-php.html#RiskCheck.riskCheckGetRiskPersonCompanyReport
    
        Parameters:
            string $gender - Gender of the person. M or F, may be empty.
            string $initials - The initials, mandatory.
            string $prefix - The surname prefix, like "van" or "de", may be empty.
            string $last_name - The last name of the person, mandatory.
            string $birth_date - Birth date in the format yyyy-mm-dd, may be empty.
            string $street - Street part of the address, may be empty.
            int $house_number - House number, mandatory.
            string $house_number_addition - Extension part of the house number, may be empty.
            string $postcode - Dutch postcode in the format 1234AB, mandatory.
            string $city - City, may be empty.
    

        Returns:
            out		- A <RiskPersonCompanyReport> entry
    */
    public function riskCheckGetRiskPersonCompanyReport($gender, $initials, $prefix, $last_name, $birth_date, $street, $house_number, $house_number_addition, $postcode, $city)
    {
        return $this->_client->riskCheckGetRiskPersonCompanyReport(['gender' => $gender, 'initials' => $initials, 'prefix' => $prefix, 'last_name' => $last_name, 'birth_date' => $birth_date, 'street' => $street, 'house_number' => $house_number, 'house_number_addition' => $house_number_addition, 'postcode' => $postcode, 'city' => $city]);
    }

    /*
        Returns a description of the route calculated between two addresses.
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerGetRoute
    
        Parameters:
            string $start_postcode - Start address postcode.
            int $start_house_number - Start address house number.
            string $start_house_number_addition - Start address house number addition.
            string $start_street - Start address street.
            string $start_city - Start address city.
            string $start_country - Start address country.
            string $destination_postcode - Destination address postcode.
            int $destination_house_number - Destination address house number.
            string $destination_house_number_addition - Destination address house number addition.
            string $destination_street - Destination address street.
            string $destination_city - Destination address city.
            string $destination_country - Destination address country.
            string $route_type - Type of route to calculate:
                    fastest      -- Calculates the fastest route. [default]
                    shortest     -- The shortest possible route.
                    economic     -- The most economic and/or most environmental friendly route.
            string $language - Language of the description, currently supports:
                    danish       -- danske
                    english      -- english
                    french       -- français
                    german       -- Deutsch
                    italian      -- italiano
                    dutch        -- nederlands
    

        Returns:
            route  - A <RoutePlannerRoute> entry.
    */
    public function routePlannerGetRoute($start_postcode, $start_house_number, $start_house_number_addition, $start_street, $start_city, $start_country, $destination_postcode, $destination_house_number, $destination_house_number_addition, $destination_street, $destination_city, $destination_country, $route_type, $language)
    {
        return $this->_client->routePlannerGetRoute(['start_postcode' => $start_postcode, 'start_house_number' => $start_house_number, 'start_house_number_addition' => $start_house_number_addition, 'start_street' => $start_street, 'start_city' => $start_city, 'start_country' => $start_country, 'destination_postcode' => $destination_postcode, 'destination_house_number' => $destination_house_number, 'destination_house_number_addition' => $destination_house_number_addition, 'destination_street' => $destination_street, 'destination_city' => $destination_city, 'destination_country' => $destination_country, 'route_type' => $route_type, 'language' => $language]);
    }

    /*
        Returns the route distance and driving time between two addresses.
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerInformationAddress
    
        Parameters:
            string $routetype - Type of route to calculate, 'fastest', 'shortest' or 'economic'
            string $from_postalcode - Start address postal code
            string $from_houseno - Start address house number
            string $from_street - Start address street
            string $from_city - Start address city
            string $from_country - Start country (ISO3, ISO2 or Full-Text)
            string $to_postalcode - Destination address postal code
            string $to_houseno - Destination address house-number
            string $to_street - Destination address street
            string $to_city - Destination address city
            string $to_country - Destination country (ISO3, ISO2 or Full-Text)
    

        Returns:
            route - A <RouteInfo> entry.
    */
    public function routePlannerInformationAddress($routetype, $from_postalcode, $from_houseno, $from_street, $from_city, $from_country, $to_postalcode, $to_houseno, $to_street, $to_city, $to_country)
    {
        return $this->_client->routePlannerInformationAddress(['routetype' => $routetype, 'from_postalcode' => $from_postalcode, 'from_houseno' => $from_houseno, 'from_street' => $from_street, 'from_city' => $from_city, 'from_country' => $from_country, 'to_postalcode' => $to_postalcode, 'to_houseno' => $to_houseno, 'to_street' => $to_street, 'to_city' => $to_city, 'to_country' => $to_country]);
    }

    /*
        Returns a description of the route calculated between two addresses
        For every part of the route the drivetime and drivedistance
        are given. The description is available in several languages controlled by
        the language parameter. The fastest, most economic or shortest route
        can be calculated
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerDescriptionAddress
    
        Parameters:
            string $routetype - Type of route to calculate, 'fastest', 'shortest' or 'economic'
            string $from_postalcode - Start address postal code
            string $from_houseno - Start address house number
            string $from_street - Start address street
            string $from_city - Start address city
            string $from_country - Start country (ISO3, ISO2 or Full-Text)
            string $to_postalcode - Destination address postal code
            string $to_houseno - Destination address house-number
            string $to_street - Destination address street
            string $to_city - Destination address city
            string $to_country - Destination country (ISO3, ISO2 or Full-Text)
            string $language - Language of the description: 'danish', 'dutch', 'english', 'french', 'german' or 'italian'
    

        Returns:
            route  - A <Patterns::{Type}Array> of <RoutePart> entries.
    */
    public function routePlannerDescriptionAddress($routetype, $from_postalcode, $from_houseno, $from_street, $from_city, $from_country, $to_postalcode, $to_houseno, $to_street, $to_city, $to_country, $language)
    {
        return $this->_client->routePlannerDescriptionAddress(['routetype' => $routetype, 'from_postalcode' => $from_postalcode, 'from_houseno' => $from_houseno, 'from_street' => $from_street, 'from_city' => $from_city, 'from_country' => $from_country, 'to_postalcode' => $to_postalcode, 'to_houseno' => $to_houseno, 'to_street' => $to_street, 'to_city' => $to_city, 'to_country' => $to_country, 'language' => $language]);
    }

    /*
        Returns a description of the fastest route between two dutch postcodes.
        For every part of the route the drivetime in seconds and drivedistance
        in meters are given as well. The description is available in dutch
        and english, depending on the english parameter toggle.
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerDescription
    
        Parameters:
            string $postcodefrom - Postcode at the start of the route
            string $postcodeto - Postcode at the end of the route
            boolean $english - Whether to returns the description in english (true)
                    or dutch (false)
    

        Returns:
            route  - A <Patterns::{Type}Array> of <RoutePart> entries.
    */
    public function routePlannerDescription($postcodefrom, $postcodeto, $english)
    {
        return $this->_client->routePlannerDescription(['postcodefrom' => $postcodefrom, 'postcodeto' => $postcodeto, 'english' => $english]);
    }

    /*
        Returns a description of the shortest route between two dutch postcodes.
        For every part of the route the drivetime in seconds and drivedistance
        in meters are given as well. The description is available in dutch
        and english, depending on the english parameter toggle.
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerDescriptionShortest
    
        Parameters:
            string $postcodefrom - Postcode at the start of the route
            string $postcodeto - Postcode at the end of the route
            boolean $english - Whether to returns the description in english (true)
                    or dutch (false)
    

        Returns:
            route  - A <Patterns::{Type}Array> of <RoutePart> entries.
    */
    public function routePlannerDescriptionShortest($postcodefrom, $postcodeto, $english)
    {
        return $this->_client->routePlannerDescriptionShortest(['postcodefrom' => $postcodefrom, 'postcodeto' => $postcodeto, 'english' => $english]);
    }

    /*
        Returns a description of the route between two dutch postcodes,
        including the RD coordinates along the route.
        For every part of the route the drivetime in seconds and drivedistance
        in meters are given as well.
        The routetype can be shortest, economic or fastest. By default the fastest route will be calculated.
        The description is available in dutch and english, depending on the english parameter toggle.
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerDescriptionCoordinatesRD
    
        Parameters:
            string $postcodefrom - Postcode at the start of the route
            string $postcodeto - Postcode at the end of the route
            string $routetype - Type of route to calculate: 'fastest', 'shortest' or 'economic'
            boolean $english - Whether to returns the description in english (true)
                    or dutch (false)
    

        Returns:
            route  - A <RouteDescriptionCoordinatesRD> entry.
    */
    public function routePlannerDescriptionCoordinatesRD($postcodefrom, $postcodeto, $routetype, $english)
    {
        return $this->_client->routePlannerDescriptionCoordinatesRD(['postcodefrom' => $postcodefrom, 'postcodeto' => $postcodeto, 'routetype' => $routetype, 'english' => $english]);
    }

    /*
        Returns the route distance and driving time between two dutch postcodes.
        The routetype can be shortest, economic or fastest. By default the fastest route will be calculated.
        Methods that works on neighborhoodcodes can be found in the <Driveinfo> service.
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerInformation
    
        Parameters:
            string $postcodefrom - Postcode at the start of the route
            string $postcodeto - Postcode at the end of the route
            string $routetype - Type of route to calculate: 'fastest', 'shortest' or 'economic'
    

        Returns:
            route - A <RouteInfo> entry.
    */
    public function routePlannerInformation($postcodefrom, $postcodeto, $routetype)
    {
        return $this->_client->routePlannerInformation(['postcodefrom' => $postcodefrom, 'postcodeto' => $postcodeto, 'routetype' => $routetype]);
    }

    /*
        Returns a description of the route between two coordinates in the RD
        (Rijksdriehoeksmeting) coordinate system.
        For every part of the route the drivetime in seconds and drivedistance
        in meters are given as well. The description is available in dutch
        and english, depending on the english parameter toggle.
        The fastest, economic or shortest route can be calculated depending on the
        routetype parameter.
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerRDDescription
    
        Parameters:
            int $xfrom - x part of the RD coordinate at the start of the route
            int $yfrom - y part of the RD coordinate at the start of the route
            int $xto - x part of the RD coordinate at the end of the route
            int $yto - y part of the RD coordinate at the end of the route
            string $routetype - Type of route to calculate: 'fastest', 'shortest' or 'economic'
            boolean $english - Whether to returns the description in english (true)
                    or dutch (false)
    

        Returns:
            route  - A <Patterns::{Type}Array> of <RoutePart> entries.
    */
    public function routePlannerRDDescription($xfrom, $yfrom, $xto, $yto, $routetype, $english)
    {
        return $this->_client->routePlannerRDDescription(['xfrom' => $xfrom, 'yfrom' => $yfrom, 'xto' => $xto, 'yto' => $yto, 'routetype' => $routetype, 'english' => $english]);
    }

    /*
        Returns the route distance and drive time between two coordinates in the RD
        (Rijksdriehoeksmeting) coordinate system.
        The fastest, economic or shorted route can be calculated depending on the routetype
        parameter.
        Methods that works on neighborhoodcodes can be found in the <Driveinfo> service.
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerRDInformation
    
        Parameters:
            int $xfrom - x part of the RD coordinate at the start of the route
            int $yfrom - y part of the RD coordinate at the start of the route
            int $xto - x part of the RD coordinate at the end of the route
            int $yto - y part of the RD coordinate at the end of the route
            string $routetype - Type of route to calculate: 'fastest', 'shortest' or 'economic'
    

        Returns:
            route - A <RouteInfo> entry.
    */
    public function routePlannerRDInformation($xfrom, $yfrom, $xto, $yto, $routetype)
    {
        return $this->_client->routePlannerRDInformation(['xfrom' => $xfrom, 'yfrom' => $yfrom, 'xto' => $xto, 'yto' => $yto, 'routetype' => $routetype]);
    }

    /*
        Returns a description of the route between two coordinates in the RD
        (Rijksdriehoeksmeting) coordinate system, including the RD coordinates along the route.
        For every part of the route the drivetime in seconds and drivedistance
        in meters are given as well.
        The fastest, economic or shortest route can be calculated depending on the
        routetype parameter.
        The description is available in dutch and english, depending on the english parameter toggle.
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerRDDescriptionCoordinatesRD
    
        Parameters:
            int $xfrom - x part of the RD coordinate at the start of the route
            int $yfrom - y part of the RD coordinate at the start of the route
            int $xto - x part of the RD coordinate at the end of the route
            int $yto - y part of the RD coordinate at the end of the route
            string $routetype - Type of route to calculate: 'fastest', 'shortest' or 'economic'
            boolean $english - Whether to returns the description in english (true)
                    or dutch (false)
    

        Returns:
            route  - A <RouteDescriptionCoordinatesRD> entry.
    */
    public function routePlannerRDDescriptionCoordinatesRD($xfrom, $yfrom, $xto, $yto, $routetype, $english)
    {
        return $this->_client->routePlannerRDDescriptionCoordinatesRD(['xfrom' => $xfrom, 'yfrom' => $yfrom, 'xto' => $xto, 'yto' => $yto, 'routetype' => $routetype, 'english' => $english]);
    }

    /*
        Returns the route distance and driving time between two dutch addresses.
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerInformationDutchAddress
    
        Parameters:
            string $routetype - Type of route to calculate, 'fastest', 'shortest' or 'economic'
            string $from_postalcode - Start address postal code
            string $from_housno - Start address house number
            string $from_street - Start address street
            string $from_city - Start address city
            string $to_postalcode - Destination address postal code
            string $to_housno - Destination address house-number
            string $to_street - Destination address street
            string $to_city - Destination address city
    

        Returns:
            route - A <RouteInfo> entry.
    */
    public function routePlannerInformationDutchAddress($routetype, $from_postalcode, $from_housno, $from_street, $from_city, $to_postalcode, $to_housno, $to_street, $to_city)
    {
        return $this->_client->routePlannerInformationDutchAddress(['routetype' => $routetype, 'from_postalcode' => $from_postalcode, 'from_housno' => $from_housno, 'from_street' => $from_street, 'from_city' => $from_city, 'to_postalcode' => $to_postalcode, 'to_housno' => $to_housno, 'to_street' => $to_street, 'to_city' => $to_city]);
    }

    /*
        Returns a description of the route calculated between two dutch addresses
        For every part of the route the drivetime and drivedistance
        are given as well. The description is available in several languages depending
        on the language parameter. The fastest, most economic or shortest route
        can be calculated
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerDescriptionDutchAddress
    
        Parameters:
            string $routetype - Type of route to calculate, 'fastest', 'shortest' or 'economic'
            string $from_postalcode - Start address postal code
            string $from_housno - Start address house number
            string $from_street - Start address street
            string $from_city - Start address city
            string $to_postalcode - Destination address postal code
            string $to_housno - Destination address house-number
            string $to_street - Destination address street
            string $to_city - Destination address city
            string $language - Language of the description: 'danish', 'dutch', 'english', 'french', 'german' or 'italian'
    

        Returns:
            route  - A <Patterns::{Type}Array> of <RoutePart> entries.
    */
    public function routePlannerDescriptionDutchAddress($routetype, $from_postalcode, $from_housno, $from_street, $from_city, $to_postalcode, $to_housno, $to_street, $to_city, $language)
    {
        return $this->_client->routePlannerDescriptionDutchAddress(['routetype' => $routetype, 'from_postalcode' => $from_postalcode, 'from_housno' => $from_housno, 'from_street' => $from_street, 'from_city' => $from_city, 'to_postalcode' => $to_postalcode, 'to_housno' => $to_housno, 'to_street' => $to_street, 'to_city' => $to_city, 'language' => $language]);
    }

    /*
        Returns a description of the route between two latitude/longitude coordinates
        in Europe.
        For every part of the route the drivetime and drivedistance
        are given as well. The description is available in several languages depending
        on the language parameter. The fastest, economic or shortest route
        is calculated depending on the routetype parameter.
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerEUDescription
    
        Parameters:
            float $latitudefrom - Latitude of the start of the route
            float $longitudefrom - Longitude of the start of the route
            float $latitudeto - Latitude of the end of the route
            float $longitudeto - Longitude of the end of the route
            string $routetype - Type of route to calculate: 'fastest', 'shortest' or 'economic'
            string $language - Language of the description:
                    'danish', 'dutch', 'english', 'french', 'german', 'italian' or 'swedish'
    

        Returns:
            route  - A <Patterns::{Type}Array> of <RoutePart> entries.
    */
    public function routePlannerEUDescription($latitudefrom, $longitudefrom, $latitudeto, $longitudeto, $routetype, $language)
    {
        return $this->_client->routePlannerEUDescription(['latitudefrom' => $latitudefrom, 'longitudefrom' => $longitudefrom, 'latitudeto' => $latitudeto, 'longitudeto' => $longitudeto, 'routetype' => $routetype, 'language' => $language]);
    }

    /*
        Returns the route distance and drive time for the route between two
        latitude/longitude coordinates in Europe.
        The fastest, economic or shortest route can be calculated depending on the routetype
        parameter.
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerEUInformation
    
        Parameters:
            float $latitudefrom - Latitude of the start of the route
            float $longitudefrom - Longitude of the start of the route
            float $latitudeto - Latitude of the end of the route
            float $longitudeto - Longitude of the end of the route
            string $routetype - Type of route to calculate: 'fastest', 'shortest' or 'economic'
    

        Returns:
            route - A <RouteInfo> entry.
    */
    public function routePlannerEUInformation($latitudefrom, $longitudefrom, $latitudeto, $longitudeto, $routetype)
    {
        return $this->_client->routePlannerEUInformation(['latitudefrom' => $latitudefrom, 'longitudefrom' => $longitudefrom, 'latitudeto' => $latitudeto, 'longitudeto' => $longitudeto, 'routetype' => $routetype]);
    }

    /*
        Returns a description of the route between two latitude/longitude
        coordinates in Europe, including the latitude/longitude coordinates along the route.
        For every part of the route the drivetime in seconds and drivedistance
        in meters are given as well. The routetype can be shortest, economic or fastest.
        By default the fastest route will be calculated.
        The description is available in several languages depending on the language parameter.
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerEUDescriptionCoordinatesLatLon
    
        Parameters:
            float $latitudefrom - Latitude of the start of the route
            float $longitudefrom - Longitude of the start of the route
            float $latitudeto - Latitude of the end of the route
            float $longitudeto - Longitude of the end of the route
            string $routetype - Type of route to calculate: 'fastest', 'shortest' or 'economic'
            string $language - Language of the description:
                    'danish', 'dutch', 'english', 'french', 'german', 'italian' or 'swedish'
    

        Returns:
            route  - A <RouteDescriptionCoordinatesLatLon> entry.
    */
    public function routePlannerEUDescriptionCoordinatesLatLon($latitudefrom, $longitudefrom, $latitudeto, $longitudeto, $routetype, $language)
    {
        return $this->_client->routePlannerEUDescriptionCoordinatesLatLon(['latitudefrom' => $latitudefrom, 'longitudefrom' => $longitudefrom, 'latitudeto' => $latitudeto, 'longitudeto' => $longitudeto, 'routetype' => $routetype, 'language' => $language]);
    }

    /*
        Notice:
        This method is deprecated and will be removed
        
        Returns a map showing the route between two latitude/longitude coordinates in Europe.
    
        http://webview.webservices.nl/documentation/files/service_routeplanner-class-php.html#Routeplanner.routePlannerEUMap
    
        Parameters:
            float $latitudefrom - Latitude of the start of the route
            float $longitudefrom - Longitude of the start of the route
            float $latitudeto - Latitude of the end of the route
            float $longitudeto - Longitude of the end of the route
            string $routetype - Type of route to calculate, 'fastest', 'shortest' or 'economic'
            string $language - Preferred output language
                    'danish', 'dutch', 'english', 'french', 'german', 'italian' or 'swedish'
            string $format - Image format. Supported formats: 'JPG', 'PNG'
            int $width - Width in pixels, domain [80 - 800]
            int $height - Height in pixels, domain [80 - 800]
            string $view - Which part of the route to dispay. Possible values:
                    'start' (display the starting point),
                    'end'   (display the destination),
                    'overview' (display the complete route, default)
    

        Returns:
            image - A JPEG or PNG image, base64 encoded.
    */
    public function routePlannerEUMap($latitudefrom, $longitudefrom, $latitudeto, $longitudeto, $routetype, $language, $format, $width, $height, $view)
    {
        return $this->_client->routePlannerEUMap(['latitudefrom' => $latitudefrom, 'longitudefrom' => $longitudefrom, 'latitudeto' => $latitudeto, 'longitudeto' => $longitudeto, 'routetype' => $routetype, 'language' => $language, 'format' => $format, 'width' => $width, 'height' => $height, 'view' => $view]);
    }

    /*
        Convert a local Basic Bank Account Number (BBAN) to its International Bank Account Number (IBAN) counterpart.
        
        From the 1st of August 2014 the SEPA payment standards will become mandatory for payments within the SEPA area.
        All local BBAN need to be converted to IBAN to comply with these regulations.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $bban - Basic Bank Account Number (BBAN)
            string $country_iso - The ISO2 or ISO3 country code for the given BBAN number (default: NL)
    

        Returns:
            out - <SepaBankAccountData>
    */
    public function sepaConvertBasicBankAccountNumber($bban, $country_iso)
    {
        return $this->_client->sepaConvertBasicBankAccountNumber(['bban' => $bban, 'country_iso' => $country_iso]);
    }

    /*
        Validate format of an International Bank Account Number (IBAN)
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $iban - International Bank Account Number (IBAN)
    

        Returns:
            out - <SepaInternationalBankAccountNumberFormatValidationReport>
    */
    public function sepaValidateInternationalBankAccountNumberFormat($iban)
    {
        return $this->_client->sepaValidateInternationalBankAccountNumberFormat(['iban' => $iban]);
    }

    /*
        Validate a VAT number.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $vat_number - The vat number to check, starting with a country code followed by a 2 - 12 character long vat number.
    

        Returns:
            out - <VatValidation>
    */
    public function vatValidate($vat_number)
    {
        return $this->_client->vatValidate(['vat_number' => $vat_number]);
    }

    /*
        The service method is a proxy to the VIES service, please take notice of the disclaimer in <Proxy service methods>.
    
        http://webview.webservices.nl/documentation
    
        Parameters:
            string $vat_number - The vat number to check, starting with a country code followed by a 2 - 12 character long vat number.
    

        Returns:
            out - <VatProxyViesCheckVatResponse>
    */
    public function vatViesProxyCheckVat($vat_number)
    {
        return $this->_client->vatViesProxyCheckVat(['vat_number' => $vat_number]);
    }
}
