<?php

/**
 * Created by Cristian.
 * Date: 04/10/16 11:32 PM.
 */

namespace tinymeng\Laravel\Coders\Model\Relations;

use Illuminate\Support\Str;

class ReferenceFactory
{
    /**
     * @var array
     */
    protected $related;

    /**
     * @var \tinymeng\Laravel\Coders\Model\Model
     */
    protected $parent;

    /**
     * @var \tinymeng\Laravel\Coders\Model\Model[]
     */
    protected $references = [];

    /**
     * ReferenceFactory constructor.
     *
     * @param array $related
     * @param \tinymeng\Laravel\Coders\Model\Model $parent
     */
    public function __construct($related, $parent)
    {
        $this->related = (array) $related;
        $this->parent = $parent;
    }

    /**
     * @return \tinymeng\Laravel\Coders\Model\Relation[]
     */
    public function make()
    {
        if ($this->hasPivot()) {
            $relations = [];

            foreach ($this->references as $reference) {
                $relation = new BelongsToMany($this->getRelatedReference(), $reference['command'], $this->parent, $this->getRelatedModel(), $reference['model']);
                $relations[$relation->name()] = $relation;
            }

            return $relations;
        }

        return [new HasOneOrManyStrategy($this->getRelatedReference(), $this->parent, $this->getRelatedModel())];
    }

    /**
     * @return bool
     */
    protected function hasPivot()
    {
        $pivot = $this->getRelatedBlueprint()->table();
        $firstRecord = $this->parent->getRecordName();

        // See whether this potencial pivot table has the parent record name in it.
        // Not sure whether we should only take into account composite primary keys.
        if (
            ! Str::contains($pivot, $firstRecord)
        ) {
            return false;
        }

        $pivot = str_replace($firstRecord, '', $pivot);

        foreach ($this->getRelatedBlueprint()->relations() as $reference) {
            if ($reference == $this->getRelatedReference()) {
                continue;
            }

            $target = $this->getRelatedModel()->makeRelationModel($reference);

            // Check whether this potential pivot table has the target record name in it
            if (Str::contains($pivot, $target->getRecordName())) {
                $this->references[] = [
                    'command' => $reference,
                    'model' => $target,
                ];
            }
        }

        return count($this->references) > 0;
    }

    /**
     * @return \Illuminate\Support\Fluent
     */
    protected function getRelatedReference()
    {
        return $this->related['reference'];
    }

    /**
     * @return \tinymeng\Laravel\Coders\Model\Model
     */
    protected function getRelatedModel()
    {
        return $this->related['model'];
    }

    /**
     * @return \tinymeng\Laravel\Meta\Blueprint
     */
    protected function getRelatedBlueprint()
    {
        return $this->related['blueprint'];
    }
}
