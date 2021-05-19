<?php
namespace App\Entity;
use App\Entity\Matiere;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;

class MatiereSearch
{
 /**
 * @ORM\ManyToOne(targetEntity="App\Entity\Matiere")
 */
public $Matiers;

 public function getMatiere(): ?Matiere
 {
 return $this->Matiers;
 }

 public function setMatiere(?Matiere $Matiers): self
 {
 $this->Matiers = $Matiers;
 return $this;
 }

}