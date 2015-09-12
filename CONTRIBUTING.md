# Contribution Guidelines

I work really hard to provide high-quality code, I really happy if you want to contribute to this project, but here is many things that you needs to be comply.

## Issues and pull-requests

I use GitHub only for mirroring this project, the main location is a Phabricator instance that set-up on my development machine, please use this for issue creation and pull request submission.
The registration is available via github, so you can easy to create an account and start contributing.

Phabricator location: [Link](http://project.zolli.hu)

## Coding Guideline

#### Basic definitions

 * Indent with **4 space** instead of tabs. 
 * Always use simple quotation mark ( ' ) to define a string.
 * File encoding **must be** UTF-8 without BOM.
 * PHP closing tag **must be** omitted when the file only contains PHP code.
 * Lines maximum length is 120 character.

#### Location of namespace definition
Namespace definition must be going to the same line as the php opening tag (usally is the first line)
and the opening tag and the definition must be separated by **one space**.

Namespace naming must be match to [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) standard.

```php
   <?php namespace Foo\Bar\Baz;
   
   ...
```

#### Class comments
Tha initial comment of the class going after the defined **use** statements. The structure of this 
comment block is strict, each block in this comment separated with an empty line. The class 
comment and the class definition **not be** separated by an empty line.

The first block is the actual comment that describe the class functionality.

The second block is a single line comment that is a single identifier for the actual project.

The third block is the main information of the author(s) and package information, If the class
have more author all author must be go to a new line. 

The fourth block contains all meta information of the class, is like link, license, copyright,
since, etc...

Tha last fifth block is optional, here you can specify tags for various tool behaviour, 
like this example.

```php
...

/**
 * Hello class, that makes PHP able to say hello nicely
 *
 * BuildR PHP Framework
 *
 * @author <AUTHOR_FULL_NAME> <<AUTHOR_EMAIL>>
 * @package buildr
 * @subpackage Hello
 *
 * @copyright    Copyright 2015, <AUTHOR_FULL_NAME>.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 *
 * @codeCoverageIgnore
 * @cosingStandardIgnore
 */
 class Hello {
 
 ...
```

#### Use statements

The use statements go after the namespace definition, all use statement going to new line
The trailing slashes **not** be included in the definition. This is **not expected** but 
recommended to try grouping of these imports, like in the below example. Don't import
a class unless you use them.

If you use a class that in the same namespace as the another class, tha PHP not force you
to import this class, but in this standard these classes **must** be imported.

```php
    <?php namespace Foo\Bar
    
    use Baz\Baz;
    use Bar\Baz;
    use Bar\Class;
    use Bar\Hello;
    use Another\Class;
    
    /**
     * Documentation block...
     */
     
     ...
```

#### Class, traits and interface definition

Class, traits and interface names must be in **StudlyCase** format, in the class
naming you should try to assign a name that fully describe its functionality. 

The trait and interface name **must be** include its type, like this:

```
ContainerAwareTrait
GreetingsInterface
RuleInterface
```

After the definition you must insert a blank line.

implements and extends keyword must be going to the same line as the class definition.

```php
...

/**
 * Documentation block...
 */
class HelloWorld extends World implements GreetingsInterface {

    public $message = 'Hello World';
...
```

#### Class property naming and comments

Tha properties name must be formatted with **camelCase** and all property 
**must have a valid docBlock** All property docBlock must container at least on
definition, and it is the type definition of the given property. The definition
**must be** defined with **@type** definition.

For extending compatibility the class name defined with @type definition **must be**
include a trailing slash.

If you use the Buildr framework Container automatic injection functionality the definition
must be going to the first line of the block.

You allow to declare additional documentation of property, if you defined this must be
separated with a blank line.

```php
...

class Hello {

    /**
     * @type string
     */
    public $message = 'Hello World!';
    
    /**
     * Extended documentation for this property 
     *
     * @Wire
     * @type \buildr\Logger\LoggerInterface
     */
    public $logger;
..
```

#### Definitions of booleans and constants

If you write boolean and null values always be defined as *UPPERCASE* format. Same for constants 
but spaces must be replaced with _ (underscore) character.

If a class has any constant is **must be** the first definition of the class body.

```php
...

class Hello {
    
    const HELLO_WORLD = 'Hello World!';
        
    public $boolean = TRUE;
    
    public $nullValue = NULL;
    
    ...

```

#### Method definition

Methods **must be** defined in **camelCase** format and the visibility definition is **must be** the first 
keyword, additional modifiers, like abstract, static, final going **after** the visibility definition.

Opening braces **must be** goung to the same line as the function definition.

The method arguments **must be** defined in **camelCase** format.

Getters and setters always started with the proper **set**  or **get** world, following by tha class
property that it returns or sets.

```php
    ...
    
    public function setWriteToOutput($writeToOutput = FALSE) {
        $this->writeToOutput = $writeToOutput;
    }
    
    public static final function () {...}
    ...
```

If the method ends with a return statement the returns must be separated with one blank line. If
your method only an if statement feel free to use short if syntax.

The methods documentation only includes a @return statement when its returns something, if the
method is return the current object itself **do not use $this**, use the FQCN instead.

```php
...

    /**
     * @return \buildr\Logger\LoggerInterface
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
        
        return $this;
    }
    
    /**
     * Returns the current element is valid or not
     *
     * @return boolean 
     */
    public function isValid() {
        return ($this->isValid === TRUE) ? TRUE : FALSE;
    }

...
```

Use single-line type definition when the IDE not able to correctly resolve a variable type.

```php
    ...
    
    public function testFunction() {
        /** @type $container \buildr\Container\ContainerInterface */
        $container = Application::getContainer();
    }
    
    ...
```

When a method have many arguments as the method definition exceeded the line length, arguments
must be break to new lines.

```php
...

    public function testFunction(ClassTypeHint $className, 
        $argument, $
        anotherArgument) {
            ...    
    }
...
```

#### Array syntax

Always us the short array syntax that introduced in PHP 5.3. The array element **must be** going 
to new line after the definition and each element **must be** going to a new line 
and **ends** with a ',' (comma).

```php
...

class Hello {

    $array = [
        'Hello', 
        'World',
    ];
```

#### Function calls

When a function call with all arguments is longer then the 120 character line limit arguments must be 
broken down to separated lines with one indention from the start of the function call.
The method closing ')' **must be** going to the same line as the last argument.

```php
...

    $this->sayHello($argument1,
        $argument2,
        $argument3,
        $argument4);

...
```

#### If, else, elseif statements

The if body must be indented with on indention from the definition. Use `else if` instead of PHP 
short definition (`elseif`).

Opening braces **must be** going to the same line as the statement.

```php
...

    if($hello == 'world') {
        ...
    } else if($hello == $currentUser) {
        ...
    } else {
        ...
    }

...
```

#### Switch statements

Switch statements open bracket must be going to the same line as definition. `case` **must be** 
indented with one indention from the main switch definition.

`break` and other execution breaking keywords must be indented the same level as the `case` body
and **must be** separated be one blank line.

```php
...

    switch($expression) {
        case 1:
            $x = 1;
            
            break;
        case 2:
        case 3:
        case 4:
            $x = 2;
            
            break;
        default:
            $x = 0;
            
            break;
    }

...
```

### While and do-while

Closing braces must be going to the same line as the definition, the body must be indented with
one indention from the definition. 

In a do-while block, tha while keyword condition **must not** be separated white spaces.

```php
...

    while($var) {
        ...
    }
    
    do {
    
    } while($condition === TRUE);

...
```

#### For and Foreach

Closing braces must be going to the same line as the definition, the body must be indented with
one indention from the definition. 

In a for loop the three argument that separated with ';', the semicolon must be surrounded with spaces.

```php
...

    for($i = 0 ; $i < 10 ; $i++) {
        ...
    }
    
    foreach($value as $key => $value) {
        ...
    }

...
```

#### Try, catch block

Closing braces must be going to the same line as the definition, the body must be indented with
one indention from the definition. 

The `catch` argument **not be** separated with spaces from the `catch` keyword.

```php
...

    try {
        $this->functionThatMayDropException();
    } catch(InvalidArgumentException $e) {
        ...
    } catch(LogicException $e) {
        ...
    }

...
```
