<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * MarcoPoloExpansions implementation : © Hershey Sakhrani <hersh16@yahoo.com> & Vinicius Rezende <vinicius@rezende.dev>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 *
 * marcopoloexpansions.action.php
 *
 * MarcoPoloExpansions main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/marcopoloexpansions/marcopoloexpansions/myAction.html", ...)
 *
 */
class action_marcopoloexpansions extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if (self::isArg('notifwindow')) {
      $this->view = "common_notifwindow";
      $this->viewArgs['table'] = self::getArg("table", AT_posint, true);
    } else {
      $this->view = "marcopoloexpansions_marcopoloexpansions";
      self::trace("Complete reinitialization of board game");
    }
  }

  public function undo()
  {
    self::setAjaxMode();
    $this->game->undo();
    self::ajaxResponse();
  }

  public function pickCharacter()
  {
    self::setAjaxMode();
    $character_type = self::getArg("character_type", AT_posint, true);
    $this->game->pickCharacter($character_type);
    self::ajaxResponse();
  }

  public function pickGoalCards()
  {
    self::setAjaxMode();
    $card_ids = self::getArg("card_ids", AT_alphanum, true);
    $card_ids = explode("_", $card_ids);
    $this->game->pickGoalCards($card_ids);
    self::ajaxResponse();
  }

  public function pickCompensation()
  {
    self::setAjaxMode();
    $num_camels = self::getArg("camel", AT_posint, true);
    $num_coins = self::getArg("coin", AT_posint, true);
    $this->game->pickCompensation($num_camels, $num_coins);
    self::ajaxResponse();
  }

  public function placeDie()
  {
    self::setAjaxMode();
    $place = self::getArg("place", AT_alphanum, true);
    $index = self::getArg("index", AT_posint, true);
    $award_index = self::getArg("award_index", AT_posint, true);
    $flat_dice_ids = self::getArg("die_ids", AT_alphanum, true);
    $gift_free_placement_id = self::getArg("gift_free_placement_id", AT_posint, false);
    $dice_ids = explode("_", $flat_dice_ids);
    $this->game->placeDie($place, $index, $award_index, $dice_ids, $gift_free_placement_id);
    self::ajaxResponse();
  }

  public function rerollDie()
  {
    self::setAjaxMode();
    $die_id = self::getArg("die_id", AT_posint, true);
    $this->game->rerollDie($die_id);
    self::ajaxResponse();
  }

  public function bumpDie()
  {
    self::setAjaxMode();
    $die_id = self::getArg("die_id", AT_posint, true);
    $up_or_down = self::getArg("up_or_down", AT_alphanum, true);
    $this->game->bumpDie($die_id, $up_or_down);
    self::ajaxResponse();
  }

  public function buyBlackDie()
  {
    self::setAjaxMode();
    $this->game->buyBlackDie();
    self::ajaxResponse();
  }

  public function changeDice()
  {
    self::setAjaxMode();
    $dice_ids = self::getArg("dice_ids", AT_alphanum, true);
    $new_value = self::getArg("new_value", AT_posint, true);
    $gift_id = self::getArg("gift_id", AT_posint, false);
    $dice_ids = explode("_", $dice_ids);
    $this->game->changeDice($dice_ids, $new_value, $gift_id);
    self::ajaxResponse();
  }

  public function fulfillContract()
  {
    self::setAjaxMode();
    $contract_id = self::getArg("contract_id", AT_posint, true);
    $this->game->fulfillContract($contract_id);
    self::ajaxResponse();
  }

  public function fulfillArghun()
  {
    self::setAjaxMode();
    $city_card_id = self::getArg("citycard_id", AT_posint, true);
    $this->game->fulfillArghun($city_card_id);
    self::ajaxResponse();
  }

  public function fulfillGift()
  {
    self::setAjaxMode();
    $gift_id = self::getArg("gift_id", AT_posint, true);
    $board_id = self::getArg("board_id", AT_posint, false);
    $this->game->fulfillGift($gift_id, $board_id);
    self::ajaxResponse();
  }

  public function travel()
  {
    self::setAjaxMode();
    $figure_id = self::getArg("figure_id", AT_posint, true);
    $dst_id = self::getArg("dst_id", AT_posint, true);
    $this->game->travel($figure_id, $dst_id);
    self::ajaxResponse();
  }

  public function triggerOtherCityBonus()
  {
    self::setAjaxMode();
    $city_bonus_type_arg = self::getArg("city_bonus_type_arg", AT_posint, true);
    $this->game->triggerOtherCityBonus($city_bonus_type_arg);
    self::ajaxResponse();
  }

  public function skipTriggerOtherCityBonus()
  {
    self::setAjaxMode();
    $this->game->skipTriggerOtherCityBonus();
    self::ajaxResponse();
  }

  public function skipTravel()
  {
    self::setAjaxMode();
    $this->game->skipTravel();
    self::ajaxResponse();
  }

  public function skipMoveTradingPost()
  {
    self::setAjaxMode();
    $this->game->skipMoveTradingPost();
    self::ajaxResponse();
  }

  public function moveTradingPost()
  {
    self::setAjaxMode();
    $trading_post_id = self::getArg("selectedTradingPostId", AT_posint, true);
    $this->game->moveTradingPost($trading_post_id);
    self::ajaxResponse();
  }

  public function skipChooseCityAward()
  {
    self::setAjaxMode();
    $this->game->skipChooseCityAward();
    self::ajaxResponse();
  }

  public function activateMultipleCityCard()
  {
    self::setAjaxMode();
    $num_times = self::getArg("num_times", AT_posint, true);
    $payment_details = self::getArg("payment_details", AT_alphanum, false);
    $this->game->activateMultipleCityCard($num_times, $payment_details);
    self::ajaxResponse();
  }

  public function activateExchangeCityCard()
  {
    self::setAjaxMode();
    $exchange_type = self::getArg("exchange_type", AT_alphanum, true);
    $this->game->activateExchangeCityCard($exchange_type);
    self::ajaxResponse();
  }

  public function pass()
  {
    self::setAjaxMode();
    $this->game->pass();
    self::ajaxResponse();
  }

  public function triggerBonus()
  {
    self::setAjaxMode();
    $bonus_id = self::getArg("bonus_id", AT_posint, true);
    $this->game->triggerBonus($bonus_id);
    self::ajaxResponse();
  }

  public function chooseResource()
  {
    self::setAjaxMode();
    $choice = self::getArg("choice", AT_alphanum, true);
    $this->game->chooseResource($choice);
    self::ajaxResponse();
  }

  public function pickContract()
  {
    self::setAjaxMode();
    $contract_id = self::getArg("contract_id", AT_posint, true);
    $replaced_contract_id = self::getArg("replaced_contract_id", AT_posint);
    $this->game->pickContract($contract_id, $replaced_contract_id);
    self::ajaxResponse();
  }

  public function skipContract()
  {
    self::setAjaxMode();
    $this->game->skipContract();
    self::ajaxResponse();
  }

  public function usePlayerPiece()
  {
    self::setAjaxMode();
    $piece_id = self::getArg("piece_id", AT_posint, true);
    $this->game->usePlayerPiece($piece_id);
    self::ajaxResponse();
  }
}
