# Triad PHP Framework

Triad PHP Framework is PHP 5.3 framework handling (HTTP or other) requests to your application that
results in response - json, php serialized, raw, template engine - smarty or custom.

This framework was done with simplicity in mind - basically it handles requests and handling exceptions. 
Custom classes (database or template engine) can be easily implemented in application 
and this framework is not trying to implement custom database or template engine class - 
instead, use the one you like the most! 

Router can handle simple requests or MVP application at full - and you can easily create inline requests 
in your application (this Framework is HMVP - check
[HMVC](http://en.wikipedia.org/wiki/Hierarchical_model%E2%80%93view%E2%80%93controller) as reference) - even
to remote server. 

# Prerequisites
- PHP 5.3 or better (for namespace support)

# Requests -> Application -> Response

Check examples of full applications that follow MVP, PHP namespaces and dependency injection design patterns. 
[Examples](https://github.com/triadphp/examples)

## Author
- [Marek Vavrecan](mailto:vavrecan@gmail.com)

## License
- [GNU General Public License, version 3](http://www.gnu.org/licenses/gpl-3.0.html)
