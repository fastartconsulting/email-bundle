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


License
-------

This bundle is under the MIT license.
