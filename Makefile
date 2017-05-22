##
 # Makefile for make to run tests without a whole lot of extra effort
 #
 # This Makefile ought to make it that much easier, faster, better, and
 # more probable that PHP syntax errors are caught, code standards are
 # enforced, and anything that can be automatically fixed is fixed.
 #
 # @author     KDA Web Technologies, Inc. <info@kdaweb.com>
 # @copyright  2017 KDA Web Technologies, Inc.
 # @license    http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 # @version    GIT: $Id$
 # @link       http://kdaweb.com/ KDA Web Technologies, Inc.
##

#
# variables
#

# PHP Code Beautifier and Fixer
phpcbf=phpcbf.phar

# PHP Code Sniffer
phpcs=phpcs.phar

# PHP linter (syntax checker)
phplint=php -l

standard=phpcs.ruleset.xml

#
# build a selector (a find filter)
#

# baseselector includes only .php files
baseselector=-name '*.php'

# standardselector filters out files in test, tests, or vendor directories
standardselector=-a -'!' -regex '.*/tests?/.*' -a -'!' -regex '.*/vendor/.*'

# extendedselector filters out PUC-related files
extendedselector=-a -'!' -regex '.*/plugin-update-checker/.*'

# put them all together...
selector=$(baseselector) $(standardselector) $(extendedselector)

all: phpcbf phpcs lint

phpcbf:
	find . $(selector) | xargs -n1 $(phpcbf) --standard=$(standard)

phpcs:
	find . $(selector) | xargs -n1 $(phpcbf) --standard=$(standard)

lint:
	find . $(selector) | xargs -n1 $(phplint)