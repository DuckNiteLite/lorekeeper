<?php namespace App\Services\Stats;

use App\Services\Service;

use DB;
use Config;

use Illuminate\Support\Arr;
use App\Models\Stats\User\Level;
use App\Models\Stats\User\UserLevelReward;
use App\Models\Stats\Character\CharacterLevel;
use App\Models\Stats\Character\CharacterLevelReward;
use App\Models\Item\Item;

class LevelService extends Service
{
 /**
     * Creates a new level.
     *
     */
    public function createLevel($data)
    {
        DB::beginTransaction();

        try {
            if(!isset($data['stat_points'])) $data['stat_points'] = 0;
            $level = Level::create($data);

            $this->populateRewards(Arr::only($data, ['rewardable_type', 'rewardable_id', 'quantity']), $level);

            return $this->commitReturn($level);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a level.
     *
     */
    public function updateLevel($level, $data)
    {
        DB::beginTransaction();

        try {
            if(!isset($data['stat_points'])) $data['stat_points'] = 0;
            $level->update($data);

            $this->populateRewards(Arr::only($data, ['rewardable_type', 'rewardable_id', 'quantity']), $level);

            return $this->commitReturn($level);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    /**
     * Deletes a level.
     *
     */
    public function deleteLevel($level)
    {
        DB::beginTransaction();

        try {

            // Check first if the level is currently owned or if some other site feature uses it
            if(DB::table('user_levels')->where('current_level', '>=', $level->level)->exists()) throw new \Exception("At least one user has already reached this level.");
            if(DB::table('prompts')->where('level_req', '>=', $level->level)->exists()) throw new \Exception("A prompt currently has this level as a requirement.");
            $level->rewards()->delete();
            $level->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /*******************************************************************************
     * 
     *  CHARACTERS
     ******************************************************************************/

    /**
     * Creates a new level.
     *
     */
    public function createCharaLevel($data)
    {
        DB::beginTransaction();

        try {
            if(!isset($data['stat_points'])) $data['stat_points'] = 0;
            $level = CharacterLevel::create($data);

            $this->populateRewards(Arr::only($data, ['rewardable_type', 'rewardable_id', 'quantity']), $level, true);

            return $this->commitReturn($level);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a level.
     *
     */
    public function updateCharaLevel($level, $data)
    {
        DB::beginTransaction();

        try {
            if(!isset($data['stat_points'])) $data['stat_points'] = 0;
            $level->update($data);

            $this->populateRewards(Arr::only($data, ['rewardable_type', 'rewardable_id', 'quantity']), $level, true);

            return $this->commitReturn($level);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    /**
     * Deletes a level.
     *
     */
    public function deleteCharaLevel($level)
    {
        DB::beginTransaction();

        try {
            // Check first if the level is currently owned or if some other site feature uses it
            if(DB::table('character_levels')->where('current_level', '>=', $level->level)->exists()) throw new \Exception("At least one character has already reached this level.");
            $level->rewards()->delete();
            $level->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /*******************************************************************************
     * 
     *  OTHER FUNCTIONS
     * 
     ******************************************************************************/

         /**
     * Processes user input for creating/updating prompt rewards.
     *
     * @param  array                      $data
     * @param  \App\Models\Prompt\Prompt  $level
     */
    private function populateRewards($data, $level, $isChara = false)
    {
        // Clear the old rewards...
        $level->rewards()->delete();
        if(!$isChara)
        {
            if(isset($data['rewardable_type'])) {
                foreach($data['rewardable_type'] as $key => $type)
                {
                    UserLevelReward::create([
                        'level_id'       => $level->id,
                        'rewardable_type' => $type,
                        'rewardable_id'   => $data['rewardable_id'][$key],
                        'quantity'        => $data['quantity'][$key],
                    ]);
                }
            }
        }
        else {
            if(isset($data['rewardable_type'])) {
                foreach($data['rewardable_type'] as $key => $type)
                {
                    CharacterLevelReward::create([
                        'level_id'       => $level->id,
                        'rewardable_type' => $type,
                        'rewardable_id'   => $data['rewardable_id'][$key],
                        'quantity'        => $data['quantity'][$key],
                    ]);
                }
            }
        }
    }
}