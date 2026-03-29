<?php

namespace ARiddlestone\DeptracCakePhp2\ReferenceExtractors\ClassRegistry;

use ClassRegistry;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use Deptrac\Deptrac\Contract\Ast\NikicReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\TypeScope;
use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;

/**
 * @implements NikicReferenceExtractorInterface<StaticCall>
 */
class InitReferenceExtractor implements NikicReferenceExtractorInterface
{
    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    public function processNode(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        TypeScope $typeScope,
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
