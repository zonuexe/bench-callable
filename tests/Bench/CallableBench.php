<?php

declare(strict_types=1);

namespace zonuexe;

use Closure;
use PhpBench\Attributes as Bench;
use Subject;
use function array_map;
use function range;

#[Bench\Iterations(20)]
#[Bench\BeforeMethods('setUp')]
class CallableBench
{
    private array $array;
    private Closure $closure;
    private Closure $staticClosure;
    private Closure $closureWrapper;
    private Closure $staticClosureWrapper;
    private array $callableArray;
    private string $callableString;
    private Closure $closureFromCallable;
    private Closure $firstclassClosure;

    public function setUp(): void
    {
        $this->array = range(0, 99);
        $this->closure = fn() => 1;
        $this->staticClosure = static fn() => 1;
        $this->closureWrapper = fn() => Subject::method();
        $this->staticClosureWrapper = static fn() => Subject::method();
        $this->callableArray = [Subject::class, 'method'];
        $this->callableString = 'Subject::method';
        $this->closureFromCallable = Closure::fromCallable([Subject::class, 'method']);
        $this->firstclassClosure = Subject::method(...);
    }

    public function benchNoop(): void
    {
        $prop = 'closureWrapper';
        $_ = $this->$prop;
        $result = [];
        foreach ($this->array as $_) {
            $result[] = 1;
        }
    }

    public function benchDirectMethodCall(): void
    {
        $prop = 'closureWrapper';
        $_ = $this->$prop;
        $result = [];
        foreach ($this->array as $_) {
            $result[] = Subject::method();
        }
    }

    /** @param array{list<mixed>} $param */
    #[Bench\ParamProviders('provideCallableProperty')]
    public function benchCallable($param): void
    {
        [$property] = $param;
        $callback = $this->$property;
        $result = [];
        foreach ($this->array as $_) {
            $result[] = $callback();
        }
    }

    /** @param array{list<mixed>} $param */
    #[Bench\ParamProviders('provideCallableProperty')]
    public function benchArrayMap($param): void
    {
        [$property] = $param;
        $callback = $this->$property;
        $result = array_map($callback, $this->array);
    }

    public function provideCallableProperty()
    {
        yield 'fn() => 1' => ['closure'];
        yield 'static fn() => 1' => ['staticClosure'];
        yield 'fn() => Subject::method()' => ['closureWrapper'];
        yield 'static fn() => Subject::method()' => ['staticClosureWrapper'];
        yield "[Subject::class, 'method']" => ['callableArray'];
        yield "'Subject::method'" => ['callableString'];
        yield "Closure::fromCallable([Subject::class, 'method'])" => ['closureFromCallable'];
        yield 'Subject::method(...)' => ['firstclassClosure'];
    }
}
