{OVERALL_GAME_HEADER}

<!--
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- MarcoPoloExpansions implementation : © Hershey Sakhrani <hersh16@yahoo.com> & Vinicius Rezende <vinicius@rezende.dev>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    marcopoloexpansions_marcopoloexpansions.tpl

    This is the HTML template of your game.

    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.

    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format

    See your "view" PHP file to check how to set variables and control blocks

    Please REMOVE this comment before publishing your game on BGA
-->
<div id="zoomBox">
    <div id="characterSelection" class="whiteblock">
        <div id="characterSelectionDescription" style="background-color:white;border-radius: 10px;padding: 20px;display:none;"></div>
    </div>
    <div id="goalSelection" class="whiteblock"></div>
    <div style="clear: both;"></div>
    <div id="boardContainer">
        <div id="mainBoardContainer">
            <div id="board">
                <div id="map_node_0" class="map_node venezia" style="top:30px;left:10px;"></div>
                <div id="map_node_1" class="map_node oasis" style="top:12px;left:102px;"></div>
                <div id="map_node_2" class="map_node large_city" style="top:8px;left:158px;"></div>
                <div id="map_node_3" class="map_node small_city" style="top:9px;left:330px;"></div>
                <div id="map_node_4" class="map_node large_city" style="top:4px;left:449px;"></div>
                <div id="map_node_5" class="map_node oasis" style="top:58px;left:568px;"></div>
                <div id="map_node_6" class="map_node oasis" style="top:10px;left:653px;"></div>
                <div id="map_node_7" class="map_node oasis" style="top:111px;left:602px;"></div>
                <div id="map_node_8" class="map_node bejing" style="top:33px;left:692px;"></div>
                <div id="map_node_9" class="map_node oasis" style="top:104px;left:97px;"></div>
                <div id="map_node_10" class="map_node large_city" style="top:120px;left:274px;"></div>
                <div id="map_node_11" class="map_node oasis" style="top:180px;left:411px;"></div>
                <div id="map_node_12" class="map_node small_city" style="top:160px;left:453px;"></div>
                <div id="map_node_13" class="map_node large_city" style="top:179px;left:546px;"></div>
                <div id="map_node_14" class="map_node small_city" style="top:156px;left:676px;"></div>
                <div id="map_node_15" class="map_node oasis" style="top:207px;left:762px;"></div>
                <div id="map_node_16" class="map_node large_city" style="top:240px;left:13px;"></div>
                <div id="map_node_17" class="map_node oasis" style="top:210px;left:149px;"></div>
                <div id="map_node_18" class="map_node small_city" style="top:224px;left:193px;"></div>
                <div id="map_node_19" class="map_node large_city" style="top:274px;left:372px;"></div>
                <div id="map_node_20" class="map_node oasis" style="top:349px;left:554px;"></div>
                <div id="map_node_21" class="map_node large_city" style="top:328px;left:643px;"></div>
                <div id="map_node_22" class="map_node oasis" style="top:390px;left:119px;"></div>
                <div id="map_node_23" class="map_node small_city" style="top:380px;left:214px;"></div>
                <div id="map_node_24" class="map_node small_city" style="top:433px;left:450px;"></div>

                <div id="board_spot_coin3_0" class="board_spot" style="top:436px;left:66px;"></div>
                <div id="board_spot_coin5_0" class="board_spot" style="top:664px;left:12px;"></div>
                <div id="board_spot_travel_0" class="board_spot" style="top:570px;left:516px;height:50px;"></div>
                <div id="award_spot_travel_0_0" class="award_spot travel" style="top:546px;left:555px;"></div>
                <div id="award_spot_travel_0_1" class="award_spot travel" style="top:546px;left:592px;"></div>
                <div id="award_spot_travel_0_2" class="award_spot travel" style="top:542px;left:629px;"></div>
                <div id="award_spot_travel_0_3" class="award_spot travel" style="top:542px;left:668px;"></div>
                <div id="award_spot_travel_0_4" class="award_spot travel" style="top:540px;left:705px;"></div>
                <div id="award_spot_travel_0_5" class="award_spot travel" style="top:540px;left:742px;"></div>
                <div id="board_spot_bazaar_0" class="board_spot" style="top:640px;left:100px;height:30px;"></div>
                <div id="award_spot_bazaar_0_0" class="award_spot" style="top:640px;left:140px;width:34px;height:30px;"></div>
                <div id="award_spot_bazaar_0_1" class="award_spot" style="top:640px;left:186px;width:40px;height:30px;"></div>
                <div id="award_spot_bazaar_0_2" class="award_spot" style="top:640px;left:236px;width:45px;height:30px;"></div>
                <div id="award_spot_bazaar_0_3" class="award_spot" style="top:636px;left:290px;width:38px;height:30px;"></div>
                <div id="award_spot_bazaar_0_4" class="award_spot" style="top:636px;left:337px;width:45px;height:30px;"></div>
                <div id="award_spot_bazaar_0_5" class="award_spot" style="top:636px;left:389px;width:44px;height:30px;"></div>
                <div id="board_spot_bazaar_1" class="board_spot" style="top:600px;left:100px;height:33px;"></div>
                <div id="award_spot_bazaar_1_0" class="award_spot" style="top:600px;left:140px;width:38px;height:34px;"></div>
                <div id="award_spot_bazaar_1_1" class="award_spot" style="top:600px;left:186px;width:41px;height:34px;"></div>
                <div id="award_spot_bazaar_1_2" class="award_spot" style="top:600px;left:236px;width:42px;height:34px;"></div>
                <div id="award_spot_bazaar_1_3" class="award_spot" style="top:600px;left:288px;width:41px;height:34px;"></div>
                <div id="award_spot_bazaar_1_4" class="award_spot" style="top:600px;left:338px;width:42px;height:34px;"></div>
                <div id="award_spot_bazaar_1_5" class="award_spot" style="top:600px;left:388px;width:42px;height:34px;"></div>
                <div id="board_spot_bazaar_2" class="board_spot" style="top:570px;left:80px;width:50px;"></div>
                <div id="award_spot_bazaar_2_0" class="award_spot" style="top:570px;left:140px;width:40px;height:30px;"></div>
                <div id="award_spot_bazaar_2_1" class="award_spot" style="top:570px;left:188px;width:40px;height:30px;"></div>
                <div id="award_spot_bazaar_2_2" class="award_spot" style="top:570px;left:236px;width:43px;height:30px;"></div>
                <div id="award_spot_bazaar_2_3" class="award_spot" style="top:570px;left:288px;width:41px;height:30px;"></div>
                <div id="award_spot_bazaar_2_4" class="award_spot" style="top:568px;left:336px;width:46px;height:32px;"></div>
                <div id="award_spot_bazaar_2_5" class="award_spot" style="top:570px;left:390px;width:42px;height:30px;"></div>
                <div id="board_spot_bazaar_3" class="board_spot" style="top:536px;left:56px;width:80px;"></div>
                <div id="award_spot_bazaar_3_0" class="award_spot" style="top:542px;left:144px;width:30px;height:25px;"></div>
                <div id="award_spot_bazaar_3_1" class="award_spot" style="top:540px;left:188px;width:40px;height:27px;"></div>
                <div id="award_spot_bazaar_3_2" class="award_spot" style="top:540px;left:236px;width:42px;height:27px;"></div>
                <div id="award_spot_bazaar_3_3" class="award_spot" style="top:540px;left:288px;width:40px;height:28px;"></div>
                <div id="award_spot_bazaar_3_4" class="award_spot" style="top:536px;left:335px;width:46px;height:31px;"></div>
                <div id="award_spot_bazaar_3_5" class="award_spot" style="top:536px;left:389px;width:40px;height:30px;"></div>
                <div id="board_spot_khan_0" class="board_spot" style="top:726px;left:12px;"></div>
                <div id="board_spot_khan_1" class="board_spot" style="top:722px;left:44px;"></div>
                <div id="board_spot_khan_2" class="board_spot" style="top:718px;left:75px;"></div>
                <div id="board_spot_khan_3" class="board_spot" style="top:716px;left:108px;"></div>
                <div id="board_spot_contracts_0" class="board_spot" style="top:708px;left:254px;"></div>
                <div id="board_spot_avail_black_die_0" class="board_spot" style="top:482px;left:191px;"></div>
                <div id="board_spot_contract_award_4" class="board_spot" style="top:672px;left:672px;"></div>
                <div id="board_spot_contract_award_5" class="board_spot" style="top:672px;left:756px;"></div>
            </div>
            <div id="roundContainer">
                <!-- <div style="position: absolute;top: 0px;"><button id="toggleBoardPieces" class="bgabutton bgabutton_blue">{HIDE_PIECES}</button></div> -->
                <div style="position: absolute;top: 0px;left:0px;text-shadow: white 0 0 10px;font-weight: bold;font-size: larger;">
                    <div>{ROUND}<span id="roundNumber">1</span><span>/5</span></div>
                </div>
                <div id="round_1" class="piece contract_back" style="top: 0px; left: 180px;display:none;"></div>
                <div id="round_2" class="piece contract_back" style="top: 0px; left: 260px;"></div>
                <div id="round_3" class="piece contract_back" style="top: 0px; left: 340px;"></div>
                <div id="round_4" class="piece contract_back" style="top: 0px; left: 420px;"></div>
                <div id="round_5" class="piece contract_back" style="top: 0px; left: 500px;"></div>
                <div id="transparent_figure" class="piece figure purple" style="filter:grayscale(1) brightness(1);top:26px;left:20px;cursor:pointer;opacity: 1;"></div>
                <div id="gift_pile" class="gift gift_back" style="top: 14px; left: 656px;"></div>
                <div id="contract_special_pile" class="piece contract_back" style="right: -70px;"></div>
            </div>
        </div>
        <div id="mats" style="width:587px;flex-grow: 1;">
            <div style="display:flex;flex-wrap:wrap;">
                <!-- BEGIN playerMat -->
                <div id="playerMat-{PLAYER_ID}" class="playerMat whiteblock" style="width:610px;flex-grow:1;margin-right:5px;margin-left:5px;">
                    <div><span style="font-weight:bold;color:#{PLAYER_COLOR};{PLAYER_BACKGROUND_COLOR}">{PLAYER_NAME}</span></div>
                    <div>
                        <span><span class="piece panel coin"></span><span id="resources-coin-{PLAYER_ID}" style="display:inline-block;margin-left:36px;margin-top:8px;margin-right:6px;"></span></span>
                        <span><span class="piece panel camel"></span><span id="resources-camel-{PLAYER_ID}" style="display:inline-block;margin-left:38px;margin-top:8px;margin-right:6px;"></span></span>
                        <span><span class="piece panel pepper"></span><span id="resources-pepper-{PLAYER_ID}" style="display:inline-block;margin-left:30px;margin-top:8px;margin-right:6px;"></span></span>
                        <span><span class="piece panel silk"></span><span id="resources-silk-{PLAYER_ID}" style="display:inline-block;margin-left:36px;margin-top:8px;margin-right:6px;"></span></span>
                        <span><span class="piece panel gold"></span><span id="resources-gold-{PLAYER_ID}" style="display:inline-block;margin-left:42px;margin-top:8px;margin-right:6px;"></span></span>
                        <span><span class="piece panel vp"></span><span id="resources-vp-{PLAYER_ID}" style="display:inline-block;margin-left:48px;margin-top:8px;margin-right:6px;"></span></span>
                        <span><span class="piece panel completed_contracts"></span><span id="contracts-complete-{PLAYER_ID}" style="display:inline-block;margin-left:44px;margin-top:8px;margin-right:4px;"></span></span>
                        <span style="white-space: nowrap;"><span id="panel-trading_post-{PLAYER_ID}" class="piece panel trading_post {PLAYER_COLOR_NAME}" style="margin-top:-8px;"></span><span id="trading-posts-left-{PLAYER_ID}" style="display:inline-block;margin-left:44px;margin-top:8px;"></span></span>
                    </div>
                    <div style="margin-top:10px;font-style: italic;font-size: small;width:calc(100% - 120px);">{TRADING_POST_POINTS_DESC}</div>
                    <div>
                        <div>
                            <div id="small-container-{PLAYER_ID}" style="position:relative;margin-top:20px;height: 40px;"></div>
                            <div id="large-container-{PLAYER_ID}" style="position:relative;margin-top:20px;height: 100px;">
                                <div class="parchment {CSS_OTHER_PLAYER_PARCHMENT}"></div>
                            </div>
                        </div>
                        <div id="myCharacterAndGoalArea-{PLAYER_ID}" style="display:none;position: relative;">
                            <span id="goal-cards-{PLAYER_ID}"></span>
                        </div>
                    </div>
                    <div id="playerCharacter-{PLAYER_ID}" style="position:absolute;right:0px;bottom:0px;"></div>
                </div>
                <!-- END playerMat -->
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var jstpl_player_panel = '<div><div id="panel_${player_id}" class="panel_player">\
    <span class="panel_resource_wrapper"><span id="panel_coin_${player_id}" class="piece panel coin"></span><span id="panel_value_coin_${player_id}" class="panel_resource"></span></span>\
    <span class="panel_resource_wrapper"><span id="panel_camel_${player_id}" class="piece panel camel"></span><span id="panel_value_camel_${player_id}" class="panel_resource"></span></span>\
    <span class="panel_resource_wrapper"><span id="panel_pepper_${player_id}" class="piece panel pepper"></span><span id="panel_value_pepper_${player_id}" class="panel_resource" style="margin-left: 30px;"></span></span>\
    <span class="panel_resource_wrapper"><span id="panel_silk_${player_id}" class="piece panel silk"></span><span id="panel_value_silk_${player_id}" class="panel_resource"></span></span>\
    <span class="panel_resource_wrapper"><span id="panel_gold_${player_id}" class="piece panel gold" style="margin-left:-3px;"></span><span id="panel_value_gold_${player_id}" class="panel_resource"></span></span>\
    <span class="panel_resource_wrapper"><span id="panel_vp_${player_id}" class="piece panel vp" style="transform:scale(0.4025);margin-top:3px;"></span><span id="panel_value_vp_${player_id}" class="panel_resource"></span></span></div>\
  </div>\
<span class="piece panel_hourglass" id="panel_hourglass_${player_id}" style="display:none;"></span>\
</div>';

var jstpl_larger_uiItem_tooltip = '<div class="mp_tooltip" style="height:100%;width:100%;padding:10px;">\
    <div class="${className}" style="position:static;transform:scale(1);transform-orgin:0px 0px;background-position:${x}px ${y}px;border:none;filter:drop-shadow(0px 0px 4px black);"></div>\
    <div style="padding-top:20px;width:${descriptionWidth};">${description}</div>\
</div>';

var jstpl_player_aid = '<div class="mp_playeraid">\
    <div class="mp_playeraid_t0 mp_playeraid_title">${player_aid_bonus_actions_title}</div>\
    <div class="mp_playeraid_t1 mp_playeraid_subtitle">${player_aid_bonus_contract}</div>\
    <div class="mp_playeraid_t2 mp_playeraid_subtitle">${player_aid_bonus_coin}</div>\
    <div class="mp_playeraid_t3 mp_playeraid_subtitle">${player_aid_bonus_reroll}</div>\
    <div class="mp_playeraid_t4 mp_playeraid_subtitle">${player_aid_bonus_adjustdie}</div>\
    <div class="mp_playeraid_t5 mp_playeraid_subtitle">${player_aid_bonus_blackdie}</div>\
    <div class="mp_playeraid_p1 mp_playeraid_content">${player_aid_bonus_contract_desc}</div>\
    <div class="mp_playeraid_p2 mp_playeraid_content">${player_aid_bonus_coin_desc}</div>\
    <div class="mp_playeraid_p3 mp_playeraid_content">${player_aid_bonus_reroll_desc}</div>\
    <div class="mp_playeraid_p4 mp_playeraid_content">${player_aid_bonus_adjustdie_desc}</div>\
    <div class="mp_playeraid_p5 mp_playeraid_content">${player_aid_bonus_blackdie_desc}</div>\
</div>';
</script>

{OVERALL_GAME_FOOTER}
