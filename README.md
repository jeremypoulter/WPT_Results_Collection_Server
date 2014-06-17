WPT Results Collection Server
=============================

File upload utility for capturing web-platform test results from devices
that are unable to download them locally.

To integrate with the web platform test runner, these files need to be
hosted at:

  http://web-platform.test/upload/

An apache2 server with PHP is required.

There are changes required to the test runner as well, so this only works
with selected versions of that.
