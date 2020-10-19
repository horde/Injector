# Upgrading code using Injector to Horde 6

## PHP Version

Horde 6 Injector now requires PHP 7.2 or higher

## Child classes / Inheriting

Child classes now must implement a compatible constructor. When creating Child Injectors, Horde 5 used to create Horde_Injector types but now creates injectors of the top most child class. See horde/components (Dependencies/Injector) for an example. 

It is advisable to wrap and use injector rather than using it as a base class.

Binder Implementations must either type hint for Horde\Injector\Binder or a more general type. Hinting for Horde_Injector_Binder will not suffice the Liskov principle and produce an error.

## Use namespaced version

When refactoring code, use Horde\Injector\Injector rather than the compatibility wrapper \Horde_Injector.

## PSR-11 PHP FIG Container Interface

Injector now implements the PSR-11 Container Interface proposed by PHP-FIG.
You can now use get() instead of getInstance() and has() instead of hasInstance(). The get() method will throw an appropriate Horde\Injector\NotFoundException when asked for a dependency not available.

The old getInstance() and hasInstance() methods are still supported for backward compatibility.
If you need to create a guaranteed fresh instance of a dependency, use createInstance() as before.

