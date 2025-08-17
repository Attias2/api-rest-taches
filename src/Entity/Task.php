<?php

namespace App\Entity;
use App\Entity\Timestampable;
use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\HasLifecycleCallbacks] //Active les PrePersist/PreUpdate
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    
    #[ORM\Column(type: "string", length: 20)]
    //validateur symfony qui teste si la valeur de statut est dans STATUSES et renvoit message si ce n'est pas le cas
    #[Assert\Choice(choices: Task::STATUSES, message: "Choisissez un statut valide.")]
    private string $status;
    public const STATUSES = ['en retard', 'en cours', 'terminée'];

    //utilisation de la classe Timestampable pour avoir les champs creat_at et updat_at
    //qui permettent de connaitre les moments de créations et de modifications
    use Timestampable;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
