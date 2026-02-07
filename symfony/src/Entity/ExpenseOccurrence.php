<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: "App\Repository\ExpenseOccurrenceRepository")]
#[ORM\HasLifecycleCallbacks]
class ExpenseOccurrence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Expense::class, inversedBy: "occurrences")]
    #[ORM\JoinColumn(nullable: false)]
    private $expense;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: "date")]
    private $occurrenceDate;

    #[ORM\Column(type: "string", length: 20, nullable: false, options: ["default" => "unpaid"])]
    #[Assert\Choice(choices: ["unpaid", "paid", "partially_paid"], message: "Choose a valid payment status.")]
    private $paymentStatus = 'unpaid';

    #[ORM\Column(type: "date", nullable: true)]
    private $paymentDate;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: false)]
    private $amount;

    #[ORM\Column(type: "datetime", nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    private $createdAt;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTime();
    }

    // Gettery i settery

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpense(): ?Expense
    {
        return $this->expense;
    }

    public function setExpense(Expense $expense): self
    {
        $this->expense = $expense;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getOccurrenceDate(): ?\DateTimeInterface
    {
        return $this->occurrenceDate;
    }

    public function setOccurrenceDate(\DateTimeInterface $occurrenceDate): self
    {
        $this->occurrenceDate = $occurrenceDate;
        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;
        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?\DateTimeInterface $paymentDate): self
    {
        $this->paymentDate = $paymentDate;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    // Metody pomocnicze
    public function isPaid(): bool
    {
        return $this->paymentStatus === 'paid';
    }

    public function isUnpaid(): bool
    {
        return $this->paymentStatus === 'unpaid';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->paymentStatus === 'partially_paid';
    }
}
