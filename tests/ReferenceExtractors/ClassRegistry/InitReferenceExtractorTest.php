<?php

namespace ARiddlestone\DeptracCakePhp2\Tests\ReferenceExtractors\ClassRegistry;

use ARiddlestone\DeptracCakePhp2\ReferenceExtractors\ClassRegistry\InitReferenceExtractor;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use ModelClass;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InitReferenceExtractor::class)]
class InitReferenceExtractorTest extends TestCase
{
    #[DataProvider('getFilePaths')]
    public function testClassRegistryInit(string $filePath): void
    {
        $parser = $this->createParser($filePath);
        $astFileReference = $parser->parseFile($filePath);
        $astClassReferences = $astFileReference->classLikeReferences;
        self::assertCount(1, $astClassReferences[1]->dependencies);

        self::assertSame(
            ModelClass::class,
            $astClassReferences[1]->dependencies[0]->token->toString(),
        );
    }

    public static function getFilePaths(): array
    {
        return [
            'ClassRegistryInitWithString' => [__DIR__.'/Fixtures/ClassRegistryInitWithString.php'],
            'ClassRegistryInitWithPluginString' => [__DIR__.'/Fixtures/ClassRegistryInitWithPluginString.php'],
            'ClassRegistryInitWithArray' => [__DIR__.'/Fixtures/ClassRegistryInitWithArray.php'],
            'ClassRegistryInitWithPluginArray' => [__DIR__.'/Fixtures/ClassRegistryInitWithPluginArray.php'],
        ];
    }

    #[DataProvider('getFailingFilePaths')]
    public function testClassRegistryInitFailing(string $filePath): void
    {
        $parser = $this->createParser($filePath);
        $astFileReference = $parser->parseFile($filePath);
        $astClassReferences = $astFileReference->classLikeReferences;
        self::assertCount(0, $astClassReferences[1]->dependencies);
    }

    public static function getFailingFilePaths(): array
    {
        return [
            'WrongClass' => [__DIR__.'/Fixtures/WrongClass.php'],
            'WrongMethod' => [__DIR__.'/Fixtures/WrongMethod.php'],
            'MissingParameter' => [__DIR__.'/Fixtures/MissingParameter.php'],
            'MissingArrayItem' => [__DIR__.'/Fixtures/MissingArrayItem.php'],
        ];
    }

    private function createParser(string $filePath): NikicPhpParser
    {
        $cache = new AstFileReferenceInMemoryCache();
        $extractors = [
            new InitReferenceExtractor(),
        ];

        return new NikicPhpParser(
            (new ParserFactory())->createForNewestSupportedVersion(),
            $cache,
            $extractors,
        );
    }
}
