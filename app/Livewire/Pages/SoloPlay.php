<?php

namespace App\Livewire\Pages;

use App\Models\Raffle;
use App\Models\RaffleTicket;
use App\Models\UserTicket;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SoloPlay extends Component
{
    public $tickets;
    public $tiles = [];
    public $revealedTiles = [];
    public $currentRow;
    public $gameActive = false;
    public $correctTiles = [];
    public $viewData = [];
    public $raffle;
    public $earnedEntries = 0;
    public $awaitingDecision = false;
    public $usedTicketId = null;


    public function mount(Raffle $raffle)
    {
        $this->raffle = $raffle;
        $this->raffle->prize = json_decode($this->raffle->prize, true);
        $this->tickets = auth()->user()->ticket_balance;
        $this->initializeGame();
    }

    public function initializeGame()
    {
        $this->tiles = array_fill(0, 10, 'hidden');
        $this->revealedTiles = [];
        $this->currentRow = 4;
        $this->updateViewData();
    }

    public function startGame()
    {
        $user = auth()->user();

        $alreadySecured = RaffleTicket::where('user_id', $user->id)
            ->where('raffle_id', $this->raffle->id)
            ->count();

        $maxAllowed = $this->raffle->max_entries_per_user ?? PHP_INT_MAX;
        $remainingAllowed = $maxAllowed - $alreadySecured;

        if ($remainingAllowed <= 0) {
            alert_error("🎟️ You've reached the maximum entries allowed for this raffle.");
            return;
        }

        $userHasEntries = RaffleTicket::where('raffle_id', $this->raffle->id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$userHasEntries && $this->raffle->slots <= 0) {
            alert_error('Raffle is full. No slots left.');
            return;
        }

        if ($this->tickets <= 0) {
            alert_error('No tickets left.');
            return;
        }

        DB::beginTransaction();

        try {
            $ticket = UserTicket::where('user_id', $user->id)
                ->where('status', 'available')
                ->lockForUpdate()
                ->first();

                // dd($ticket);

            if (!$ticket) {
                DB::rollBack();
                alert_error('No available user ticket to start the game.');
                return;
            }

            $ticket->update(['status' => 'consumed']);
            $this->usedTicketId = $ticket->id;
            $user->decrement('ticket_balance');
            if (!$userHasEntries) {
            $this->raffle->decrement('slots');
            }

            DB::commit();

            $this->tickets = $user->fresh()->ticket_balance;
            $this->initializeGame();
            $this->gameActive = true;
            $this->dispatch('play-sound', sound: 'play');

        } catch (\Exception $e) {
            DB::rollBack();
            alert_error('Failed to start game. Try again.');
        }
    }

    public function revealTile($index)
    {
        if (!$this->gameActive) {
            alert_error('Click play to start the game.');
            return;
        }

        $rowStart = $this->currentRow * 2;
        $rowEnd = $rowStart + 1;

        if ($index < $rowStart || $index > $rowEnd) {
            alert_error('Choose a tile from the current row.');
            return;
        }

        $isWin = mt_rand(1, 100) <= 40;

        if ($isWin) {
            $this->tiles[$rowStart] = 'ticket';
            $this->tiles[$rowEnd] = 'ticket';
            $this->revealedTiles[] = $rowStart;
            $this->revealedTiles[] = $rowEnd;

            $this->dispatch('play-sound', sound: 'correct');

            if ($this->currentRow == 0) {
                $this->earnedEntries = 5;
                alert_success('🎉 You built the tower and won 5 entries!');
                $this->secureEntries();
            } else {
                $this->earnedEntries = 5 - $this->currentRow;
                $this->currentRow--;
                $this->awaitingDecision = true;
                $this->gameActive = false;
            }
        } else {
            $this->tiles[$index] = 'empty';
            $this->gameActive = false;
            $this->awaitingDecision = false;
            alert_error('💥 Wrong tile! Game over. Try again.');
            $this->dispatch('play-sound', sound: 'wrong');
        }

        $this->updateViewData();
    }

    public function secureEntries()
    {
        $user = auth()->user();

        if ($this->earnedEntries <= 0) {
            alert_error("No entries earned to secure.");
            return;
        }

        $alreadySecured = RaffleTicket::where('user_id', $user->id)
            ->where('raffle_id', $this->raffle->id)
            ->count();

        $maxAllowed = $this->raffle->max_entries_per_user ?? PHP_INT_MAX;
        $remainingAllowed = $maxAllowed - $alreadySecured;

        if ($remainingAllowed <= 0) {
            alert_error("🎟️ You've reached the maximum entries allowed for this raffle.");
            return;
        }

        $toSecure = min($this->earnedEntries, $remainingAllowed);

        DB::beginTransaction();

        try {
            if (!$this->usedTicketId) {
                DB::rollBack();
                alert_error("No ticket available from this session to secure entries.");
                return;
            }

            $userTicket = UserTicket::where('id', $this->usedTicketId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$userTicket) {
                DB::rollBack();
                alert_error("Used ticket not found.");
                return;
            }

            $newToRaffle = !RaffleTicket::where('user_id', $user->id)
                ->where('raffle_id', $this->raffle->id)
                ->exists();

            RaffleTicket::create([
                'user_id' => $user->id,
                'raffle_id' => $this->raffle->id,
                'user_ticket_id' => $userTicket->id,
                'ticket_number' => $userTicket->ticket_number,
            ]);

            $userTicket->update(['status' => 'assigned']);


            if ($newToRaffle && $this->raffle->available_slots > 0) {
                $this->raffle->decrement('available_slots');
            }

            DB::commit();

            alert_success("🎉 Secured 1 entry in the raffle!");

            $this->earnedEntries = 0;
            $this->awaitingDecision = false;
            $this->gameActive = false;
            $this->usedTicketId = null;
            $this->initializeGame();
            $this->updateViewData();

        } catch (\Exception $e) {
            DB::rollBack();
            alert_error("Failed to secure entries. Please try again.");
        }
    }


    public function continueGame()
    {
        $this->awaitingDecision = false;
        $this->gameActive = true;
    }

    public function updateViewData()
    {
        $this->viewData = [
            'tiles' => $this->tiles,
            'tickets' => $this->tickets,
        ];
    }

    public function render()
    {
        return view('livewire.pages.solo-play')->layout('components.layouts.app');
    }
}
