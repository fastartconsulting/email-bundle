FastART Consulting 
=============

EmailBundle v1.0 STABLE
=============

Integrated with:
- 
FACUserBundle


Setup
------------
In services,yml 

    fac_user_bundle.email_service:
        class: FAC\EmailBundle\Utils\EmailProcessProvider
        #class: FAC\UserBundle\Utils\EmailProcess
        public: true


- Run Server and Enjoy ;) 

After 
```
php bin/console doctrine:schema:update --force
php bin/console server:start
```

License
-------

This bundle is under the MIT license.
