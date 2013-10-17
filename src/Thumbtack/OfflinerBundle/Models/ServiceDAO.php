<?php

namespace Application\Model;
use \PDO;
use \PDOException;
use Application\Entity\SitePageEntity;

class IndexerDAO {

    public function regProcess($count){
        $this->DBH->beginTransaction();
        if($this->getProcessCount() < $count){
            $STH = $this->DBH->prepare("INSERT INTO `service_info` VALUES ()");
            $STH->execute();
            $id = $this->DBH->lastInsertId();
            $this->DBH->commit();
            return $id;
        }else{
            $this->DBH->rollBack();
            return null;
        }
    }
    public function unregProcess($id){
       $STH =  $this->DBH->prepare("DELETE FROM `service_info` WHERE `process_id` = :id ");
       $STH->bindParam(':id',$id);
       $STH->execute();
    }
    public function repairSchema(){
        $this->DBH->exec('
        DROP TABLE IF EXISTS `service_info`;
        CREATE  TABLE `service_info` (
          `process_id` INT NOT NULL AUTO_INCREMENT ,
          PRIMARY KEY (`process_id`) )
        ENGINE = InnoDB
        DEFAULT CHARACTER SET = utf8;
        ');
        $pr = IndexerModel::STATUS_INDEXING;
        $q = IndexerModel::STATUS_AWAITING;
        $this->DBH->exec("
          UPDATE `pages` SET `status` = $q WHERE `status` = $pr
        ");
    }

}
