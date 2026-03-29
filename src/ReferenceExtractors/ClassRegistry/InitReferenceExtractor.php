<?php

namespace ARiddlestone\DeptracCakePhp2\ReferenceExtractors\ClassRegistry;

use ClassRegistry;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use Deptrac\Deptrac\Contract\Ast\PHPStanReferenceExtractorInterface;
use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\MutatingScope;

/**
 * @implements PHPStanReferenceExtractorInterface<StaticCall>
 */
class InitReferenceExtractor implements PHPStanReferenceExtractorInterface
{
    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * @param StaticCall $node
     * @param ReferenceBuilderInterface $referenceBuilder
     * @param MutatingScope $scope
     * @return void
     */
    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        MutatingScope $scope,
    ): void {
        if ($node->class->name != ClassRegistry::class) {
            return;
        }
        if ($node->name->name != 'init') {
            return;
        }
        if (!isset($node->args[0])) {
            return;
        }

        $value = $node->args[0]->value;

        if ($value instanceof Array_) {
            $classItems = array_values(
                array_filter(
                    $value->items,
                    fn(ArrayItem $item) => $item->key instanceof String_ && $item->key->value == 'class',
                ),
            );

            if (!$classItems) {
                return;
            }

            $value = $classItems[0]->value;
        }

        if ($value instanceof String_) {
            $value = preg_replace('/^.*\./', '', $value->value);
            $referenceBuilder->dependency(
                ClassLikeToken::fromFQCN($value),
                $node->getLine(),
                DependencyType::VARIABLE,
            );
        }
    }
}
