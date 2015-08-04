<?php

namespace SimpleSurvivalGames;

use pocketmine\scheduler\PluginTask;

class Timer extends PluginTask{

    private $ssg;

    public function __construct(Main $ssg){
        parent::__construct($ssg);
        $this->ssg = $ssg;
        $this->minute = $this->ssg->settings["wait"] + $this->ssg->settings["play"] + $this->ssg->settings["deathmatch"];
        $this->total = $this->minute;
    }

    public function onRun($tick){
        $this->minute -= 1;
        $totalPlay = $this->total - $this->ssg->settings["wait"];
        $dm = $totalPlay - $this->ssg->settings["play"];
        if($this->minute > $totalPlay){
            $this->ssg->getServer()->broadcastMessage("SG Starting in ".$this->minute - $totalPlay." minutes");
        }elseif($this->minute === $totalPlay){
            if(count($this->ssg->getServer()->getOnlinePlayers()) < $this->ssg->settings["min-players"]){
                $this->ssg->getServer()->broadcastMessage("Not enough players to start a match right now");
                $this->minute += 1;
            }else{
                $this->ssg->getServer()->broadcastMessage("SG Starts now!");
                $this->ssg->game = true;
            }
        }elseif($this->minute < $totalPlay and $this->minute > $dm){
            $this->ssg->getServer()->broadcastMessage("Deathmatch starting in ".$this->minute - $dm." minutes");
        }elseif($this->minute === $dm){
            $this->ssg->freeSpawns();
            foreach($this->ssg->getServer()->getOnlinePlayers() as $p){
                $p->teleport($this->ssg->getFreeSpawn($p));
                $p->sendMessage("Deathmatch has started!");
            }
        }elseif($this->minute < $dm and $this->minute > 0){
            $this->ssg->getServer()->broadcastMessage("Match ending in ".$this->minute." minutes");
        }elseif($this->minute === 0){
            $this->ssg->end();
        }
    }

}