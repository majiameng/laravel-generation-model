<?php

use Illuminate\Support\Fluent;
use tinymeng\Laravel\Coders\Model\Factory;
use tinymeng\Laravel\Coders\Model\Model;
use tinymeng\Laravel\Coders\Model\Relations\BelongsTo;
use tinymeng\Laravel\Meta\Blueprint;

class ModelTest extends TestCase
{
    public function dataForTestPhpTypeHint()
    {
        return [
            'Non-nullable int' => [
                'castType' => 'int',
                'nullable' => false,
                'expect' => 'int',
            ],
            'Nullable int' => [
                'castType' => 'int',
                'nullable' => true,
                'expect' => 'int|null',
            ],
            'Non-nullable json' => [
                'castType' => 'json',
                'nullable' => false,
                'expect' => 'array',
            ],
            'Nullable json' => [
                'castType' => 'json',
                'nullable' => true,
                'expect' => 'array|null',
            ],
            'Non-nullable date' => [
                'castType' => 'date',
                'nullable' => false,
                'expect' => '\Carbon\Carbon',
            ],
            'Nullable date' => [
                'castType' => 'date',
                'nullable' => true,
                'expect' => '\Carbon\Carbon|null',
            ],
        ];
    }

    /**
     * @dataProvider dataForTestPhpTypeHint
     *
     * @param string $castType
     * @param bool $nullable
     * @param string $expect
     */
    public function testPhpTypeHint($castType, $nullable, $expect)
    {
        $model = new Model(
            new Blueprint('test', 'test', 'test'),
            new Factory(
                \Mockery::mock(\Illuminate\Database\DatabaseManager::class),
                \Mockery::mock(Illuminate\Filesystem\Filesystem::class),
                \Mockery::mock(\tinymeng\Laravel\Support\Classify::class),
                new \tinymeng\Laravel\Coders\Model\Config()
            )
        );

        $result = $model->phpTypeHint($castType, $nullable);
        $this->assertSame($expect, $result);
    }

    /**
     * @dataProvider provideDataForTestNullableRelationships
     * @param bool $nullable
     * @param string $expectedTypehint
     */
    public function testBelongsToNullableRelationships($nullable, $expectedTypehint)
    {
        $columnDefinition = new Fluent(
            [
                'nullable' => $nullable,
            ]
        );

        $baseBlueprint = Mockery::mock(Blueprint::class);
        $baseBlueprint->shouldReceive('columns')->andReturn([$columnDefinition]);
        $baseBlueprint->shouldReceive('schema')->andReturn('test');
        $baseBlueprint->shouldReceive('qualifiedTable')->andReturn('test.test');
        $baseBlueprint->shouldReceive('connection')->andReturn('test');
        $baseBlueprint->shouldReceive('primaryKey')->andReturn(new Fluent(['columns' => []]));
        $baseBlueprint->shouldReceive('relations')->andReturn([]);
        $baseBlueprint->shouldReceive('table')->andReturn('things');
        $baseBlueprint->shouldReceive('column')->andReturn($columnDefinition);

        $model = new Model(
            $baseBlueprint,
            new Factory(
                \Mockery::mock(\Illuminate\Database\DatabaseManager::class),
                \Mockery::mock(Illuminate\Filesystem\Filesystem::class),
                \Mockery::mock(\tinymeng\Laravel\Support\Classify::class),
                new \tinymeng\Laravel\Coders\Model\Config()
            )
        );

        $relation = new BelongsTo(
            new Fluent([
                'columns' => [
                    $columnDefinition
                ]
            ]),
            $model,
            $model
        );

        $this->assertSame($expectedTypehint, $relation->hint());
    }

    public function provideDataForTestNullableRelationships()
    {
        return [
            'Nullable Relation' => [
                true, '\\\\Thing|null'
            ],
            'Non Nullable Relation' => [
                false, '\\\\Thing'
            ]
        ];
    }
}
