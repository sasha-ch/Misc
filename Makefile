#------------------------------------------------------------------------------
# MAIN
#------------------------------------------------------------------------------
init:
        @$(call PRINT_INFO, "composer install")
        @composer install

        @$(call PRINT_INFO, "setfacl of var/cache var/logs")
        setfacl -R -m u:"www-data":rwX -m u:`whoami`:rwX var/cache var/logs
        setfacl -dR -m u:"www-data":rwX -m u:`whoami`:rwX var/cache var/logs

        @$(call PRINT_INFO, "default code style")
        ./bin/phpcs --config-set installed_paths vendor/escapestudios/symfony2-coding-standard

        @$(call PRINT_INFO, "git/hooks")
        rm -rf .git/hooks && ln -s ../hooks .git/hooks && chmod +x hooks/*

dump:
        @$(call PRINT_INFO, "DB dump load")
        mkdir -p dumps/
        ssh <login>@<host> -C 'pg_dump <db>' > dumps/`date +'%Y.%m.%d-%H:%M:%S'`.sql

#------------------------------------------------------------------------------
# SHORTCUTS
#------------------------------------------------------------------------------
cc:
        php bin/console cache:clear

dmd:
        php bin/console doctrine:migrations:diff

dmm:
        php bin/console doctrine:migrations:migrate

#------------------------------------------------------------------------------
# VARS
#------------------------------------------------------------------------------
define PRINT_INFO
        echo "\033[1;46m $1 \033[0m"
endef
