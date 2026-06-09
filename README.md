# vanhoagoc

## ※ Service Version
- Laravel Version: 11.9.2
- PHP Version: 8.3
- MariaDB Version: 10.4.25

##### Config step:
1. Clone project:
    - Clone with HTTPS:
        ```
         $ https://github.com/gotech-dev/vanhoagoc_new.git
        ```
    - Or with SSH:
        ```
        $ git@github.com:gotech-dev/vanhoagoc_new.git
        ```
2. Enter the project after clone and copy .env.example -> .env (all of environment of project):
    ```
    $ cd vanhoagoc_new
    $ git checkout main
    $ cp .env.example .env
    ```
3. Change .env file
   Replace key value
    ```
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=default
    DB_USERNAME=root
    DB_PASSWORD=
    ```
4. Run command
   ```
   $ composer install
   $ npm install
   $ php artisan key:generate
   $ php artisan migrate
   $ php artisan storage:link
   ```
## ※ Code design pattern
- Repository
- Service
#### Folder structure:
    app/
        Console/
        Exceptions/
        Helpers/
        Http/
        Models/
        Services/
        Repositories/
        ...
    ...

## Coding style
### 1. PHP Tag
PHP code MUST use the long <?php ?> tags or the short-echo <?= ?> tags; it MUST NOT use the other tag variations.
### 2. Namespace, Use Declarations and Class Names
- Namespaces and classes MUST follow an "autoloading" PSR: [PSR-0, PSR-4].
- When present, there MUST be one blank line after the namespace declaration.
- When present, all use declarations MUST go after the namespace declaration.
- There MUST be one use keyword per declaration.
- There MUST be one blank line after the use block.
- Class names MUST be declared in StudlyCaps.
### 3. Class Constants, Properties, and Methods
- Constants: constants MUST be declared in all upper case with underscore separators. For example: DATE_APPROVED.
- Properties: Whatever naming convention is used SHOULD be applied consistently within a reasonable scope. This project will use camelCase.
- Methods: Method names MUST be declared in camelCase().
### 4. Files
- All PHP files MUST end with a single blank line.
- The closing ?> tag MUST be omitted from files containing only PHP.
### 5. Lines
- The soft limit on line length MUST be 120 characters; automated style checkers MUST warn but MUST NOT error at the soft limit.
- Lines SHOULD NOT be longer than 80 characters; lines longer than that SHOULD be split into multiple subsequent lines of no more than 80 characters each.
- There MUST NOT be trailing whitespace at the end of non-blank lines.
- There MUST NOT be more than one statement per line.
### 6. Indenting
Code MUST use an indent of 4 spaces, and MUST NOT use tabs for indenting.
### 7. Keywords and True/False/Null
- PHP keywords MUST be in lower case. For example: isset(), array().
- The PHP constants true, false, and null MUST be in lower case.
### 8. Class, Interfaces and Traits.
- 8.1. Extends and Implements:
    - The extends and implements keywords MUST be declared on the same line as the class name.
    - The opening brace for the class MUST go on its own line; the closing brace for the class MUST go on the next line after the body.
        ```sh
        <?php
            namespace Vendor\Package;
            
            use FooClass;
            use BarClass as Bar;
            use OtherVendor\OtherPackage\BazClass;
            
            class ClassName extends ParentClass implements \ArrayAccess, \Countable
            {
                // constants, properties, methods
            }
        ```
    - Lists of implements MAY be split across multiple lines, where each subsequent line is indented once. When doing so, the first item in the list MUST be on the next line, and there MUST be only one interface per line.
        ```sh
        <?php
            namespace Vendor\Package;
            
            use FooClass;
            use BarClass as Bar;
            use OtherVendor\OtherPackage\BazClass;
            
            class ClassName extends ParentClass implements
                \ArrayAccess,
                \Countable,
                \Serializable
            {
                // constants, properties, methods
            }
        ```
- 8.2. Properties:
    - Visibility MUST be declared on all properties.
    - There MUST NOT be more than one property declared per statement.
    - Property names SHOULD NOT be prefixed with a single underscore to indicate protected or private visibility.
- 8.3. Methods:
    - Visibility MUST be declared on all methods.
    - Method names SHOULD NOT be prefixed with a single underscore to indicate protected or private visibility.
    - Method names MUST NOT be declared with a space after the method name. The opening brace MUST go on its own line, and the closing brace MUST go on the next line following the body. There MUST NOT be a space after the opening parenthesis, and there MUST NOT be a space before the closing parenthesis.
        ```sh
        <?php
            namespace Vendor\Package;
            
            class ClassName
            {
                public function fooBarBaz($arg1, &$arg2, $arg3 = [])
                {
                    // method body
                }
            }
        ```
- 8.4. Method Arguments:
    - In the argument list, there MUST NOT be a space before each comma, and there MUST be one space after each comma.
    - Method arguments with default values MUST go at the end of the argument list.
        ```sh
            <?php
                namespace Vendor\Package;
                
                class ClassName
                {
                    public function foo($arg1, &$arg2, $arg3 = [])
                    {
                        // method body
                    }
                }
        ```
- 8.5. abstract, final, and static
    - When present, the abstract and final declarations MUST precede the visibility declaration.
    - When present, the static declaration MUST come after the visibility declaration.
        ```sh
        <?php
        namespace Vendor\Package;
        
        abstract class ClassName
        {
            protected static $foo;
        
            abstract protected function zim();
        
            final public static function bar()
            {
                // method body
            }
        }
        ```
### 4. Control Structures
- There MUST be one space after the control structure keyword
- There MUST NOT be a space after the opening parenthesis
- There MUST NOT be a space before the closing parenthesis
- There MUST be one space between the closing parenthesis and the opening brace
- The structure body MUST be indented once
- The closing brace MUST be on the next line after the body
    ```sh
    <?php
    if ($expr1) {
        // if body
    } elseif ($expr2) {
        // elseif body
    } else {
        // else body
    }
    ```
