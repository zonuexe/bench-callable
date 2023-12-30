# bench-callable


## Run benchmark

```
composer install
php -n ./vendor/bin/phpbench run --report=consumation_of_time tests/Bench/
```

## Result

```
% sw_vers
ProductName:		macOS
ProductVersion:		14.2.1
BuildVersion:		23C71

% sysctl machdep.cpu
machdep.cpu.cores_per_package: 8
machdep.cpu.core_count: 8
machdep.cpu.logical_per_package: 8
machdep.cpu.thread_count: 8
machdep.cpu.brand_string: Apple M1 Pro

% php -nv
PHP 8.3.1 (cli) (built: Dec 20 2023 12:44:38) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.3.1, Copyright (c) Zend Technologies
```

 * `benchNoop`: This is a test to repeatedly push `1` into an array from `foreach` without calling a method.
 * `benchDirectMethodCall`: This is a test to repeatedly call a target static method directly that returns `1` and push it array from `foreach`.
 * `benchCallable`: This is a test to repeatedly call a target static method via `callable` objects and push it array from `foreach`.

A callable object is generated as follows:

``` php
$this->closure = fn() => 1;
$this->staticClosure = static fn() => 1;
$this->closureWrapper = fn() => Subject::method();
$this->staticClosureWrapper = static fn() => Subject::method();
$this->callableArray = [Subject::class, 'method'];
$this->callableString = 'Subject::method';
$this->closureFromCallable = Closure::fromCallable([Subject::class, 'method']);
$this->firstclassClosure = Subject::method(...);
```

> [!NOTE]
> Closure implicitly captures `$this`. Closure with [`static` keyword](https://www.php.net/manual/functions.anonymous.php#functions.anonymous-functions.static) avoids capturing `$this`.

```
+---------------+-----------------------+---------------------------------------------------+----------+---------+
| benchmark     | subject               | set                                               | mode     | rstdev  |
+---------------+-----------------------+---------------------------------------------------+----------+---------+
| CallableBench | benchNoop             |                                                   | 4.000μs  | ±10.53% |
| CallableBench | benchDirectMethodCall |                                                   | 5.055μs  | ±18.44% |
| CallableBench | benchCallable         | fn() => 1                                         | 5.955μs  | ±16.61% |
| CallableBench | benchCallable         | static fn() => 1                                  | 5.215μs  | ±20.91% |
| CallableBench | benchCallable         | fn() => Subject::method()                         | 7.922μs  | ±10.51% |
| CallableBench | benchCallable         | static fn() => Subject::method()                  | 7.284μs  | ±12.76% |
| CallableBench | benchCallable         | [Subject::class, 'method']                        | 7.047μs  | ±15.03% |
| CallableBench | benchCallable         | 'Subject::method'                                 | 11.018μs | ±12.01% |
| CallableBench | benchCallable         | Closure::fromCallable([Subject::class, 'method']) | 5.186μs  | ±23.57% |
| CallableBench | benchCallable         | Subject::method(...)                              | 5.434μs  | ±24.81% |
| CallableBench | benchArrayMap         | fn() => 1                                         | 3.973μs  | ±19.51% |
| CallableBench | benchArrayMap         | static fn() => 1                                  | 3.943μs  | ±23.32% |
| CallableBench | benchArrayMap         | fn() => Subject::method()                         | 5.000μs  | ±4.32%  |
| CallableBench | benchArrayMap         | static fn() => Subject::method()                  | 5.002μs  | ±6.32%  |
| CallableBench | benchArrayMap         | [Subject::class, 'method']                        | 3.945μs  | ±21.64% |
| CallableBench | benchArrayMap         | 'Subject::method'                                 | 3.992μs  | ±18.43% |
| CallableBench | benchArrayMap         | Closure::fromCallable([Subject::class, 'method']) | 3.945μs  | ±21.64% |
| CallableBench | benchArrayMap         | Subject::method(...)                              | 4.000μs  | ±13.61% |
+---------------+-----------------------+---------------------------------------------------+----------+---------+
```

 * The overhead of calling a closure created with `Closure::fromCallable()` is negligible compared to calling the method directly.
 * `static fn` is very slightly faster than `fn`, but it's not a big difference.
 * `fn() => Subject::method()` has a double call cost of closure and target method.
 * Calling a string callable repeatedly from `foreach` is slow, so if you're calling the same method over and over again, it's worth converting to [`Closure::fromCallable()`](https://www.php.net/manual/closure.fromcallable.php).
 * `array_map()` is internally optimized, so it doesn't make much difference which method you use.
