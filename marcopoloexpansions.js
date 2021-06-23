/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * MarcoPoloExpansions implementation : © Hershey Sakhrani <hersh16@yahoo.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * marcopoloexpansions.js
 *
 * MarcoPoloExpansions user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo", "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
],
    function (dojo, declare) {
        return declare("bgagame.marcopoloexpansions", ebg.core.gamegui, {
            constructor: function () {
                this.uiItems = [];
                this.myCharacterType = 0;
                this.currentMove;
                this.currentMoveArgs;
                this.canUndo = false;
                this.mainActionAvailable = false;
                this.canBuyBlackDie = false;
                this.canUsePersonalCityCard = false;
                this.playerResources = {};
                this.playerMatContainerUiTypes = { "small": ["die", "gift"], "large": ["goal_card", "contract", "1x_gift", "city_card"] };
                this.runningPlayerMatAnimations = {};
                this.blinkPassHandle = null;
            },

            //#region uiItems
            attachFunctionsToUiItems: function () {
                var _self = this;
                this.uiItems._lastUid = 0;

                this.uiItems.getByUid = function (uid) {
                    return this.find(function (u) { return u.uid == uid });
                }

                this.uiItems.getByUiType = function (uiType) {
                    return this.filter(function (u) { return u.uiType == uiType });
                }

                this.uiItems.getByUiTypeAndId = function (uiType, id) {
                    return this.find(function (u) { return u.uiType == uiType && parseInt(u.data.id) == parseInt(id) });
                }

                this.uiItems.getByUiTypeAndTypeArg = function (uiType, typeArg) {
                    return this.find(function (u) { return u.uiType == uiType && parseInt(u.data.type_arg) == parseInt(typeArg) });
                }

                this.uiItems.getByUiTypes = function (uiTypes) {
                    return this.filter(function (u) { return uiTypes.includes(u.uiType) });
                }

                this.uiItems.getPlayerUiItems = function (uiType, location, playerId) {
                    return this.filter(function (u) { return u.uiType == uiType && (u.data.player_id == playerId || u.data.location_arg == playerId) && u.data.location == location });
                }

                this.uiItems.getPlayerId = function (uiItem) {
                    var playerId = ["contract", "goal_card", "gift"].includes(uiItem.uiType) ? uiItem.data.location_arg : uiItem.data.player_id;
                    if (uiItem.uiType == "contract" && uiItem.data.location == "board") {
                        playerId = null;
                    }
                    return playerId;
                }

                this.uiItems.isPlayerItem = function (uiItem) {
                    var isPlayerItem = false;
                    if (uiItem.data.location) {
                        isPlayerItem = uiItem.data.location.endsWith("hand") || uiItem.data.location == "player_mat" || uiItem.data.location == "zombie_mat";
                    }
                    return isPlayerItem;
                }

                this.uiItems.resetSelectableAnimation = function ()          //need code to restart it - https://css-tricks.com/restart-css-animation/
                {
                    var items = this.getSelectableItems(false);
                    for (var i = 0; i < items.length; i++) {
                        items[i].htmlNode.classList.remove("selectable");
                        void items[i].htmlNode.offsetWidth;
                        items[i].htmlNode.classList.add("selectable");
                    }
                }

                this.uiItems.getSelectedItems = function () {
                    return this.filter(function (u) { return u.isSelected; });
                }

                this.uiItems.getSelectedItemsByUiType = function (uiType) {
                    return this.filter(function (u) { return u.isSelected && u.uiType == uiType; });
                }

                this.uiItems.getFirstSelectedItemByUiType = function (uiType) {
                    var items = this.getSelectedItemsByUiType(uiType);
                    return items.length > 0 ? items[0] : null;
                }

                this.uiItems.getFirstSelectedItemByUiTypes = function (uiTypes) {
                    var item = null;
                    for (var i = 0; i < uiTypes.length; i++) {
                        item = this.getFirstSelectedItemByUiType(uiTypes[i]);
                        if (item != null) { break; }
                    }
                    return item;
                }

                this.uiItems.getSelectableItems = function (includeSelected) {
                    if (includeSelected)
                        return this.filter(function (u) { return u.isSelectable; });

                    return this.filter(function (u) { return u.isSelectable && !u.isSelected; });
                }

                this.uiItems.makeSelectable = function (items) {
                    for (var i = 0; i < items.length; i++) {
                        items[i].isSelectable = true;
                        dojo.addClass(items[i].htmlNode, "selectable");
                    }
                    this.resetSelectableAnimation();
                }

                this.uiItems.toggleSelection = function (uiItem) {
                    if (uiItem.isSelectable) {
                        if (uiItem.isSelected) {
                            dojo.addClass(uiItem.htmlNode, "selectable");
                            dojo.removeClass(uiItem.htmlNode, "selected");
                        }
                        else {
                            dojo.removeClass(uiItem.htmlNode, "selectable")
                            dojo.addClass(uiItem.htmlNode, "selected");
                        }
                        uiItem.isSelected = !uiItem.isSelected;
                    }
                }

                this.uiItems.resetSelectable = function (items) {
                    for (var i = 0; i < items.length; i++) {
                        var item = items[i];
                        dojo.removeClass(item.htmlNode, "selectable");
                        dojo.removeClass(item.htmlNode, "selected");
                        item.isSelected = false;
                        item.isSelectable = false;
                    }
                }

                this.uiItems.resetAllSelectable = function () {
                    this.resetSelectable(this);
                }

                this.uiItems.resetAllNotSelected = function () {
                    this.resetSelectable(this.getSelectableItems(false));
                }

                this.uiItems.resetAllSelectableByType = function (uiType, exceptUiItem) {
                    var items = this.getByUiType(uiType);
                    items = items.filter(function (u) { return u != exceptUiItem });
                    this.resetSelectable(items);
                }

                this.uiItems.resetAllMapNodeBorders = function () {
                    var mapNodes = this.getByUiType("map_node");
                    for (var i = 0; i < mapNodes.length; i++) {
                        dojo.setStyle(mapNodes[i].htmlNode, "border", "");
                    }
                }

                this.uiItems.createItems = function (uiType, dataArray) {
                    this.createItemsViaCallback(function (d) { return uiType; }, dataArray);
                }

                this.uiItems.createItemsViaCallback = function (dataCallback, dataArray) {
                    for (var i = 0; i < dataArray.length; i++) {
                        var data = dataArray[i];
                        this.createAndAddItem(dataCallback(data), data);
                    }
                }

                //based on the image provided
                this.uiItems.itemBackgroundConfig = {
                    "city_card": { items_per_row: 10, width: 197, height: 126.5, type_property: "type_arg" },
                    "character": { items_per_row: 10, width: 199, height: 301, type_property: "character_type" },
                    "contract": { items_per_row: 10, width: 150, height: 150, type_property: "type_arg" },
                    "outpost": { items_per_row: 5, width: 168, height: 101, type_property: "type_arg" },
                    "city_bonus": { items_per_row: 10, width: 136, height: 0, type_property: "type_arg" },
                    "gift": { items_per_row: 16, width: 107, height: 96, type_property: "type_arg" },
                };

                this.uiItems.adjustBackgroundPositionForFullGoalCard = function (background) {
                    background.x += 4;
                    background.y += 110;
                    return background;
                }

                this.uiItems.getBackgroundPosition = function (uiType, typeArg) {
                    var background = { x: 0, y: 0 };
                    background.x = (typeArg % this.itemBackgroundConfig[uiType].items_per_row) * -1 * this.itemBackgroundConfig[uiType]["width"];
                    background.y = Math.floor(typeArg / this.itemBackgroundConfig[uiType].items_per_row) * -1 * this.itemBackgroundConfig[uiType]["height"];
                    return background;
                }

                this.uiItems.getBackgroundPositionForUiItem = function (uiItem) {
                    var background = { x: 0, y: 0 };

                    if (this.itemBackgroundConfig[uiItem.uiType] != undefined) {
                        var propertyName = this.itemBackgroundConfig[uiItem.uiType]["type_property"];
                        var typeArg = parseInt(uiItem.data[propertyName]);
                        background = this.getBackgroundPosition(uiItem.uiType, typeArg);
                    }
                    else if (uiItem.uiType == "goal_card") {
                        var type_arg = parseInt(uiItem.data.type_arg);
                        background.x = (type_arg % 10) * -180 - 9;
                        background.y = Math.floor(type_arg / 10) * -281 - 110;
                        if (uiItem.data.location == "goalSelection") { background = this.adjustBackgroundPositionForFullGoalCard(background); }
                    }
                    else if (uiItem.uiType == "die") {
                        background.x = dojo.hasClass(uiItem.htmlNode, "black") ? -805 : -905;
                        background.y = (parseInt(uiItem.data.value) - 1) * -100;
                    }
                    else {
                        background = null;
                    }

                    if (uiItem.uiType == "character" && uiItem.data.character_type == "0")      //mercator has different image depending on player count
                    {
                        var numPlayers = Object.keys(_self.gamedatas.players).length;
                        if (numPlayers == 4) {
                            background.x = -1791;
                            background.y = -602;
                        }
                        else if (numPlayers == 3) {
                            background.x = -1592;
                            background.y = -602;
                        }
                    }

                    return background;
                }

                this.uiItems.setBackgroundUiItem = function (uiItem) {
                    var background = this.getBackgroundPositionForUiItem(uiItem);
                    var htmlNode = uiItem.htmlNode;

                    if (background != null && uiItem.uiType == "die") {
                        htmlNode = dojo.query(".die_pip:first-child", uiItem.htmlNode)[0];
                    }

                    if (background != null) {
                        var backgroundPosition = background.x + "px" + " " + background.y + "px";
                        dojo.setStyle(htmlNode, "background-position", backgroundPosition);
                    }
                }

                this.uiItems.weightConfig = {
                    "die": 100, "die_white": 200, "die_black": 300, "gift": 400, "agent": 500, "goal_card": 600, "contract": 800, "city_card": 900, "default": 10000
                }

                this.uiItems.getWeight = function (uiItem) {
                    var weight = this.weightConfig["default"];
                    if (this.weightConfig[uiItem.uiType]) {
                        weight = this.weightConfig[uiItem.uiType];
                        if (uiItem.uiType == "die" && uiItem.data.type != "regular") {
                            weight = this.weightConfig["die_" + uiItem.data.type];
                        }
                        if (uiItem.uiType == "die") {
                            weight += parseInt(uiItem.data.value) * 10;
                        }
                    }

                    return weight;
                }

                this.uiItems.getColorName = function (uiType, params) {
                    var colourName = "";
                    if (uiType == "die" && params.type == "white" || params.type == "black") {
                        colourName = params.type;
                    }
                    else if (uiType == "die" && params.type == "fixed") {
                        colourName = _self.getUnusedColorName();
                    }
                    else {
                        colourName = _self.getColourNameForPlayerId(params.player_id);
                    }
                    return colourName;
                }

                this.uiItems.extractDescriptionFromResourceArray = function (resources) {
                    var description = "";
                    var resourceNameMap = {
                        "camel": _("camel(s)"), "pepper": _("pepper"), "silk": _("silk"), "gold": _("gold"), "black_die": _("black die"), "vp": _("victory point"),
                        "contract": _("new contract"), "travel": _("travel movement"), "choice_of_good": _("choice of pepper/silk/gold"), "2_diff_goods": _("choice of two different resources"),
                        "coin": _("coin"), "trigger_other_city_bonus": _("trigger another city bonus (you do not have to have a trading post there)"),
                        "placed_trading_post": _("placed trading post"), "gift": _("gift")
                    };
                    for (var resourceId in resources) {
                        description += resources[resourceId] + " ";
                        description += resourceNameMap[resourceId] + ", ";
                    }
                    if (description.length > 0) { description = description.substr(0, description.length - 2); }
                    return description;
                }

                this.uiItems.getTooltipDescription = function (uiItem) {
                    var description = "";
                    if (uiItem.uiType == "character") {
                        description = _(_self.gamedatas.material.character_types[uiItem.data.character_type].description);
                    }
                    else if (uiItem.uiType == "contract") {
                        var contractData = _self.gamedatas.material.contracts[uiItem.data.type];
                        var give = this.extractDescriptionFromResourceArray(contractData.cost);
                        var get = this.extractDescriptionFromResourceArray(contractData.award);;
                        description = dojo.string.substitute(_("Fulfill this contract by returning ${give_resources} in return you will receive ${get_resources}"), { contractNumber: uiItem.data.type, give_resources: give, get_resources: get });
                    }
                    else if (uiItem.uiType == "goal_card") {
                        description = _("<p>Get VP shown on card for having trading posts in both cities indicated.</p><p>Get 1/3/6/10 VP for having trading posts in multiple unique goal cities.</p><p>Points awarded at end of game.</p>")
                    }
                    else if (uiItem.uiType == "outpost") {
                        var outpostData = _self.gamedatas.material.outpost_bonuses[uiItem.data.type_arg];
                        description = dojo.string.substitute(_("<p>If you are the first to place a trading post here get outpost bonus of ${award}.</p><p>Only awarded after all your travel movements have been completed.</p>"), { award: this.extractDescriptionFromResourceArray(outpostData.award) });
                    }
                    else if (uiItem.uiType == "city_bonus") {
                        var cityBonusData = _self.gamedatas.material.city_bonuses[uiItem.data.type_arg];
                        description = dojo.string.substitute(_("<p>Immediately get ${award} once you place a trading post here, and finish all your travel movements.</p><p>You will receive this award in all future rounds as well.</p>"), { award: this.extractDescriptionFromResourceArray(cityBonusData.award) });
                    }
                    else if (uiItem.uiType == "city_card") {
                        var cityCardData = _self.gamedatas.material.city_cards[uiItem.data.type_arg];
                        if (cityCardData.description) {
                            description = _(cityCardData.description);
                        }
                        else if (cityCardData.kind == "multiple") {
                            description = dojo.string.substitute(_("Trade in sets of ${cost} for ${award} up to die value placed"), { cost: this.extractDescriptionFromResourceArray(cityCardData.cost), award: this.extractDescriptionFromResourceArray(cityCardData.award) });
                        }
                        description = dojo.string.substitute("${fullDescription}", { cardNumber: uiItem.data.type_arg, fullDescription: description });
                    }
                    else if (uiItem.uiType == "board_spot") {
                        var spotData = _self.gamedatas.material.spots.find(function (s) { return s.index == uiItem.data.index && s.is_award_spot == false && uiItem.data.place == s.place; });
                        description = _(spotData.description);
                    }
                    else if (uiItem.uiType == "award_spot") {
                        var awardData = _self.gamedatas.material.spots.find(function (s) { return s.tied_to_index == uiItem.data.tied_to_index && s.index == uiItem.data.index && s.is_award_spot && uiItem.data.place == s.place; });
                        var award = this.extractDescriptionFromResourceArray(awardData.award);
                        if (awardData.cost) {
                            var cost = this.extractDescriptionFromResourceArray(awardData.cost);
                            description = dojo.string.substitute(_("Pay ${cost} for ${award}"), { "cost": cost, "award": award });
                        }
                        else {
                            description = award;
                        }
                    }
                    else if (uiItem.uiType == "map_node" && uiItem.data.id == 8) {
                        description = _("<p><b>Beijing</b></p>");
                        description += _("<p>If you end your movement in Beijing, you place a trading post in the city as usual. Place it on the empty space showing the largest number of victory points.</p>");
                        description += _("<p>At the end of the game if you have a trading post in Beijing you score the victory points indicated by the space your trading post is on.</p>")
                        description += _("<p>If you have a trading post in Beijing, you score 1 victory point for every 2 goods you have left.  Players without a trading post in Beijing score nothing for their remaining goods.  Important: Camels are not goods!</p>")
                    }
                    return description;
                }

                this.uiItems.addTooltip = function (uiItem) {
                    var divId = dojo.getAttr(uiItem.htmlNode, 'id');
                    if (uiItem.uiType == "board_spot" || uiItem.uiType == "award_spot") {
                        _self.addTooltip(divId, this.getTooltipDescription(uiItem), "");
                    }
                    else if (uiItem.uiType == "map_node" && uiItem.data.id == 8) {
                        _self.addTooltip(divId, this.getTooltipDescription(uiItem), "");
                    }
                    else if (this.itemConfig[uiItem.uiType].tooltip) {
                        var cssClass = this.itemConfig[uiItem.uiType].cssClass;
                        var backgroundPosition = this.getBackgroundPositionForUiItem(uiItem);
                        var description = this.getTooltipDescription(uiItem);
                        var descriptionWidth = "0px";
                        if (uiItem.uiType == "goal_card") { backgroundPosition = this.adjustBackgroundPositionForFullGoalCard(backgroundPosition); }
                        if (description != "") {
                            descriptionWidth = dojo.getComputedStyle(uiItem.htmlNode).width;
                        }
                        var html = _self.format_block('jstpl_larger_uiItem_tooltip', { className: cssClass, x: backgroundPosition.x, y: backgroundPosition.y, description: description, descriptionWidth: descriptionWidth });
                        if (uiItem.uiType == "city_bonus" || uiItem.uiType == "outpost") {
                            divId = dojo.getAttr(this.getByUiTypeAndId("map_node", uiItem.data.location_arg).htmlNode, 'id');
                        }
                        _self.addTooltipHtml(divId, html, 500);
                    }
                }

                this.uiItems._extendUiItem_die = function (uiItem) {
                    var pip = dojo.create("div", { "class": "piece die_pip" });
                    dojo.place(pip, uiItem.htmlNode);
                }

                this.uiItems._extendUiItem_character = function (uiItem) {
                    if (uiItem.data.character_type == 7)        //Matteo Polo (optional contract)
                    {
                        this.createAndAddItem("character_spot", { "player_id": uiItem.data.player_id, "character_type": 7, "index": 0 });
                    }
                    else if (uiItem.data.character_type == 8)       //Fratre Nicoalo
                    {
                        this.createAndAddItem("character_spot", { "player_id": uiItem.data.player_id, "character_type": 8, "index": 0 });
                    }
                    else if (uiItem.data.character_type == 11)      //Gunj Kököchin
                    {
                        this.createAndAddItem("character_spot", { "player_id": uiItem.data.player_id, "character_type": 11, "place": "gunj", "index": 0 });
                        this.createAndAddItem("character_spot", { "player_id": uiItem.data.player_id, "character_type": 11, "place": "gunj", "index": 1 });
                    }
                }

                this.uiItems.itemConfig = {
                    "character": { cssClass: "character", "zIndex": 30, tooltip: true },
                    "character_spot": { cssClass: "character_spot", "zIndex": 50 },
                    "die": { cssClass: "piece die", color: true, "width": 32, "height": 32, "zIndex": 100 },
                    "trading_post": { cssClass: "piece trading_post", color: true, "zIndex": 200 },
                    "figure": { cssClass: "piece figure", color: true, "zIndex": 300 },
                    "city_card": { cssClass: "city_card", tooltip: true, width: 106 },
                    "city_bonus": { cssClass: "city_bonus", tooltip: true },
                    "outpost": { cssClass: "outpost", tooltip: true },
                    "contract": { cssClass: "contract", "width": 80, tooltip: true },
                    "goal_card": { cssClass: "goal_card", "width": 116, "height": 90, tooltip: true },
                    "map_node": { htmlNode: "map_node_${id}", "zIndex": 500 },
                    "board_spot": { htmlNode: "board_spot_${place}_${index}", "zIndex": 400, tooltip: false },
                    "award_spot": { htmlNode: "award_spot_${place}_${tied_to_index}_${index}" },
                    "gift": { cssClass: "gift", "width": 54, tooltip: true },
                    "1x_gift": { cssClass: "piece keep_gift_1x", "width": 80, tooltip: false }
                }

                this.uiItems.createAndAddItem = function (uiType, params) {
                    this._lastUid++;
                    var htmlNode = null;
                    var clickHandler = null;

                    if (this.itemConfig[uiType].cssClass) {
                        htmlNode = dojo.create("div", { "class": this.itemConfig[uiType].cssClass });
                        dojo.setAttr(htmlNode, "id", "uid-" + this._lastUid);
                    }
                    else {
                        htmlNode = $(dojo.string.substitute(this.itemConfig[uiType].htmlNode, params));
                    }

                    if (this.itemConfig[uiType].color) {
                        dojo.addClass(htmlNode, this.getColorName(uiType, params));
                    }

                    dojo.setAttr(htmlNode, "data-uid", "uid-" + this._lastUid);
                    clickHandler = dojo.connect(htmlNode, "onclick", _self, "onClickUiItem");

                    var item = { "uid": this._lastUid, "uiType": uiType, "data": params, "htmlNode": htmlNode, "clickHandler": clickHandler, isSelected: false, isSelectable: false, uiPosition: 0 };
                    if (this["_extendUiItem_" + uiType] != undefined) { this["_extendUiItem_" + uiType](item); }
                    this.setBackgroundUiItem(item);
                    this.push(item);
                    return item;
                }
            },
            //#endregion

            setupGoalCards: function (goalCardData) {
                this.uiItems.createItems("goal_card", goalCardData);        //create fake ones
                for (var playerId in this.gamedatas.players) {
                    var playerGoalCard = this.uiItems.getByUiType("goal_card").filter(function (g) { return g.data.location_arg == playerId; }.bind(this));
                    if (playerGoalCard.length == 4)        //set goal cards in hand or selection mode
                    {
                        dojo.forEach(playerGoalCard, function (g) {
                            g.data.location = "goalSelection";
                        });
                    }
                    if (playerGoalCard.length == 0 || playerGoalCard.length == 4) {
                        var fakeGoalCardData = [{ type: 29, type_arg: 29, location: "goal_hand", location_arg: playerId }, { type: 29, type_arg: 29, location: "goal_hand", location_arg: playerId }];
                        this.uiItems.createItems("goal_card", fakeGoalCardData);
                    }
                }
                var backOnlyCards = this.uiItems.getByUiType("goal_card").filter(function (g) { return g.data.type_arg == 29; });     //add goal card 29 class
                dojo.forEach(backOnlyCards, function (c) {
                    dojo.addClass(c.htmlNode, "goal_card_back");
                });
            },

            resetSetup: function () {
                for (var i = 0; i < this.uiItems.length; i++) {
                    if (this.uiItems[i].onClickHandle) {
                        dojo.disconnect(this.uiItems[i].onClickHandle);
                    }
                    if (this.uiItems[i].htmlNode) {
                        dojo.destroy(this.uiItems[i].htmlNode);
                    }
                }

                this.uiItems = [];
            },

            /*
                setup:

                This method must set up the game user interface according to current game situation specified
                in parameters.

                The method is called each time the game interface is displayed to a player, ie:
                _ when the game starts
                _ when a player refreshes the game page (F5)

                "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
            */
            getValuesFromObject: function (data) {
                var values = [];
                for (var key in data) {
                    values.push(data[key]);
                }
                return values;
            },

            setup: function (gamedatas) {
                console.log("Starting game setup");
                if (gamedatas.expansions.length == 0 || !gamedatas.expansions.includes("0")) {
                    this.dontPreloadImage('gifts.png');
                    dojo.setStyle("gift_pile", "display", "none");
                }

                this.resetSetup();
                this.attachFunctionsToUiItems();

                this.uiItems.createItems("contract", this.getValuesFromObject(gamedatas.contracts));
                this.uiItems.createItems("contract", this.getValuesFromObject(gamedatas.player_contracts));
                this.uiItems.createItems("gift", this.getValuesFromObject(gamedatas.player_gifts));
                this.uiItems.createItems("map_node", gamedatas.material.board);
                this.uiItems.createItemsViaCallback(function (s) { return s.is_award_spot ? "award_spot" : "board_spot" }, gamedatas.material.spots);
                this.uiItems.createItemsViaCallback(function (d) { return d.type; }, this.getValuesFromObject(gamedatas.pieces));
                this.uiItems.createItems("die", this.getValuesFromObject(gamedatas.dice));

                for (var playerId in gamedatas.players)        // Setting up player boards
                {
                    var player = gamedatas.players[playerId];
                    this.playerResources[playerId] = {};

                    dojo.forEach(['coin', 'camel', 'pepper', 'silk', 'gold', 'vp'], function (r) {
                        this.playerResources[playerId][r] = parseInt(player[r]);
                    }.bind(this));

                    var completedContracts = gamedatas.player_contracts_completed[playerId] == undefined ? 0 : parseInt(gamedatas.player_contracts_completed[playerId].num_of);
                    gamedatas.player_contracts_completed[playerId] = completedContracts;
                    dojo.setAttr($('contracts-complete-' + playerId), "innerHTML", completedContracts);

                    if (player.character_type != "99") {
                        this.uiItems.createItems("character", [{ player_id: playerId, character_type: player.character_type }]);
                        if (this.player_id == playerId) {
                            this.myCharacterType = player.character_type;
                        }
                    }
                    if (this.player_id == playerId) { dojo.setStyle($('myCharacterAndGoalArea-' + playerId), "display", "block"); }

                    dojo.place(this.format_block('jstpl_player_panel', { player_id: playerId }), $('player_board_' + playerId));
                    this.updateResourceInfo(playerId);
                    if (player.hourglass == "1") { this.updateHourGlass(playerId); }
                }

                this.setupGoalCards(this.getValuesFromObject(gamedatas.goal_cards));
                this.updateCurrentRound(gamedatas.current_round);
                this.setupNotifications();      // Setup game notifications to handle (see "setupNotifications" method below)
                this.drawUi();

                this.addTooltipToClass('.panel.trading_post', _("Number of trading posts placed + remaining"), "");
                this.addTooltipToClass('.panel.completed_contracts', _("Number of completed contracts.  At end of game 7VP to player(s) who complete the most"), "");
                this.addTooltipToClass('.panel.vp', _("Number of victory points"), "");
                this.addTooltipToClass('.panel.gold', _("Number of gold"), "");
                this.addTooltipToClass('.panel.silk', _("Number of silk"), "");
                this.addTooltipToClass('.panel.pepper', _("Number of pepper"), "");
                this.addTooltipToClass('.panel.camel', _("Number of camels"), "");
                this.addTooltipToClass('.panel.coin', _("Number of coins"), "");
                this.addTooltipToClass('.contract_back', _("Contract draw pile"), "");
                this.addTooltipToClass('.panel_hourglass', _("Hourglass, player who will start first next round.  Changes when a player travels"), "");
                this.addTooltip('transparent_figure', _("Toggle player figure transparency on board"), "");

                dojo.connect($("transparent_figure"), "onclick", this, "onClickTransparentFigure");
                dojo.connect($("preference_control_100"), "onchange", this, "onChangeGoalAnchorPreference");

                console.log("Ending game setup");
            },
            ///////////////////////////////////////////////////
            //// Game & client states
            pickCharacter: function (uiItem) {
                var selectedItems = this.uiItems.getSelectedItemsByUiType("character");

                if (this.isCurrentPlayerActive()) {
                    dojo.setStyle("btnConfirm", "display", "inline-block");
                }

                for (var i = 0; i < selectedItems.length; i++) {
                    if (selectedItems[i] != uiItem) {
                        this.uiItems.toggleSelection(selectedItems[i]);
                    }
                }
                var selectedCharacter = this.uiItems.getFirstSelectedItemByUiType("character");
                if (selectedCharacter != null) {
                    var description = _(this.gamedatas.material.character_types[selectedCharacter.data.character_type].description);
                    dojo.setStyle("characterSelectionDescription", "display", "block");
                    dojo.setAttr("characterSelectionDescription", "innerHTML", description);
                    dojo.place(this.getCharacterItemsDescription(selectedCharacter.data.character_type), "characterSelectionDescription");
                }
                else {
                    dojo.setStyle("characterSelectionDescription", "display", "none");
                }
                this.uiItems.resetSelectableAnimation();
            },

            pickGoals: function (uiItem) {
                this.showGoalAnchors(this.uiItems.getSelectedItemsByUiType("goal_card"), uiItem);
                this.uiItems.resetSelectableAnimation();
            },

            playerBonus: function (uiItem) {
                if (uiItem.uiType == "character_spot" || uiItem.uiType == "map_node") {
                    this.sendTriggerBonus(uiItem.data.bonusId);
                }
            },

            playerTurnSendAction: function (selectedDice, selectedPlace, selectedGifts, uiItem) {
                var actionSent = true;
                if (uiItem.uiType == "contract") {
                    this.sendFulfillContract(uiItem);
                }
                else if (uiItem.uiType == "award_spot") {
                    this.sendPlaceDie(selectedDice, selectedPlace, uiItem, selectedGifts);
                }
                else if (uiItem.uiType == "city_card" && uiItem.data.location === 'player_mat') {
                    this.sendFulfillArghun(uiItem);
                }
                else if (uiItem.uiType == "city_card") {
                    this.sendPlaceDie(selectedDice, uiItem, null, selectedGifts);
                }
                else if (uiItem.uiType == "board_spot" && this.getAwardSpots(uiItem, selectedDice).length == 0) {
                    this.sendPlaceDie(selectedDice, uiItem, null, selectedGifts);
                }
                else if (uiItem.uiType == "board_spot" && this.getAwardSpots(selectedPlace, selectedDice).length == 1) {
                    var awardSpot = this.getAwardSpots(selectedPlace, selectedDice);
                    this.sendPlaceDie(selectedDice, uiItem, awardSpot[0], selectedGifts);
                }
                else if (uiItem.uiType == "character_spot") {
                    this.sendPlaceDie(selectedDice, uiItem, null, selectedGifts);
                }
                else if (uiItem.uiType == "gift" && uiItem.data.type_arg == 4) {
                    this.switchToClientChangeDie(uiItem.data.id);
                }
                else if (uiItem.uiType == "gift" && uiItem.data.type_arg == 10) {
                    var boardIds = this.getValidBoardIdsFiguresAreOn(this.player_id);
                    if (boardIds.length == 0)
                        this.showMessage(_("No place to put a trading posts"), "error");
                    else if (boardIds.length == 1)
                        this.sendFulfillGift(uiItem, boardIds[0]);
                    else if (boardIds.length > 1)
                        this.switchToClientGiftPickTradingPost(uiItem.data.id, boardIds);
                }
                else if (uiItem.uiType == "gift" && uiItem.data.type_arg != 7) // do not call for free gift
                {
                    this.sendFulfillGift(uiItem, null);
                }
                else {
                    actionSent = false;
                }
                return actionSent;
            },

            playerTurn: function (uiItem) {
                var _self = this;
                var playerId = this.player_id;
                var selectableItems = [];
                var selectedDice = this.uiItems.getSelectedItemsByUiType("die");
                var selectedPlace = this.uiItems.getFirstSelectedItemByUiTypes(["board_spot", "city_card"]);
                var selectedGifts = this.uiItems.getSelectedItemsByUiType("gift");
                var actionSent = false;
                this.updatePlayerTurnButtons(selectedDice);
                this.uiItems.resetAllNotSelected();

                if (uiItem && uiItem.uiType == "gift" && uiItem.isSelected) {
                    this.uiItems.resetAllSelectableByType("gift", uiItem);
                }
                if (uiItem && uiItem.isSelected) {
                    actionSent = this.playerTurnSendAction(selectedDice, selectedPlace, selectedGifts, uiItem);
                }

                if (uiItem && uiItem.uiType == "die" && !actionSent)       //reset selected places if any die changed
                {
                    selectedPlace = null;
                    var allPlaces = this.uiItems.getByUiTypes(["board_spot", "city_card"]);
                    this.uiItems.resetSelectable(allPlaces);
                }

                if (!actionSent) {
                    selectableItems = selectableItems.concat(this.uiItems.getPlayerUiItems("die", "player_mat", playerId));
                    selectableItems = selectableItems.concat(this.getSelectableGiftUIItemsForPlayerTurnState(selectedDice, playerId));
                    if (selectedDice.length == 0) {
                        selectableItems = selectableItems.concat(this.getFulfillableContracts(playerId));
                        const city_cards = this.getSelectableCityCardsUIItemsForPlayerTurnState(playerId);
                        selectableItems = selectableItems.concat(city_cards);
                    }

                    if (this.mainActionAvailable && selectedPlace && this.getAwardSpots(selectedPlace, selectedDice).length > 1)     //place selected, show award spots
                    {
                        selectableItems.push(selectedPlace);
                        selectableItems = selectableItems.concat(this.getAwardSpots(selectedPlace, selectedDice));
                    }
                    else if (this.mainActionAvailable && selectedDice.length > 0 && selectedPlace == null) {
                        var allPlaces = this.uiItems.getByUiTypes(["board_spot", "city_card"]);
                        var filteredPlaces = allPlaces.filter(function (u) { return _self.isPlaceSpotAvailable(u, selectedDice, playerId) });
                        selectableItems = selectableItems.concat(this.getSelectableCharacterSpotUIItemsForPlayerTurnState(selectedDice, playerId));
                        this.uiItems.resetSelectable(allPlaces);
                        selectableItems = selectableItems.concat(filteredPlaces);
                    }
                    else if (!this.mainActionAvailable && selectedDice.length == 1)     //always make coin3 available
                    {
                        var coin3 = this.uiItems.getByUiType("board_spot").find(function (s) { return s.data.place == "coin3"; });
                        selectableItems.push(coin3);
                    }
                }
                this.uiItems.makeSelectable(selectableItems);
            },

            playerTravel: function (uiItem) {
                var _self = this;
                var playerId = this.player_id;
                var figures = this.uiItems.getPlayerUiItems("figure", "board", playerId);
                var selectedFigure = this.uiItems.getFirstSelectedItemByUiType("figure");
                var selectedMapNode = this.uiItems.getFirstSelectedItemByUiType("map_node");
                var gift = this.uiItems.getByUiType("gift").find(function (g) { return g.data.type_arg == 10 && g.data.location_arg == playerId; });     //only gift 10 is allowed in travel
                if (selectedFigure == null) {
                    selectedFigure = this.currentMoveArgs.selectedFigure;
                }

                this.uiItems.makeSelectable(figures);
                if (uiItem && uiItem.uiType == "gift") {
                    this.playerTurnSendAction([], [], [], uiItem);
                }
                else if (uiItem && uiItem.uiType == "figure" && figures.length > 1) {
                    selectedFigure = uiItem;
                    this.uiItems.resetAllSelectable();
                    this.uiItems.makeSelectable(figures);
                }
                else if (selectedFigure == null) {
                    selectedFigure = figures[0];
                }

                if (!selectedFigure.isSelected) {
                    this.uiItems.toggleSelection(selectedFigure);
                }

                if (figures.length > 1)     //lift over map_node if more than one choice
                {
                    if (selectedFigure && selectedMapNode) {
                        this.resetUiItemsZIndex(figures);
                    }
                    else {
                        this.forceChangeUiItemsZIndex(figures, 600);
                    }
                }

                if (gift != null) { this.uiItems.makeSelectable([gift]); }
                if (selectedFigure && selectedMapNode == null) {
                    var currentMapNode = this.uiItems.getByUiType("map_node").find(function (m) { return m.data.id == selectedFigure.data.location_arg });
                    var nodes = this.uiItems.getByUiType("map_node").filter(function (m) { return _self.isMapNodeAvailable(currentMapNode, m); });
                    this.uiItems.makeSelectable(nodes);
                }
                else if (selectedFigure && selectedMapNode) {
                    this.sendTravel(selectedFigure, selectedMapNode);
                }
            },

            playerPickContract: function () {
                var playerId = this.player_id;
                var dieValue = parseInt(this.last_server_state.args.die_value) - 1;
                var validContractIds = this.last_server_state.args.valid_contract_ids.split("_");
                var selectedContract = this.uiItems.getSelectedItems();
                var myContracts = this.uiItems.getByUiType("contract").filter(function (c) { return c.data.location == "hand" && c.data.location_arg == playerId; });

                if (selectedContract.length == 0) {
                    var contracts = this.uiItems.getByUiType("contract").filter(function (c) { return c.data.location == "board" && parseInt(c.data.location_arg) <= dieValue; });
                    this.uiItems.makeSelectable(contracts);
                }
                else if (selectedContract.length > 0 && myContracts.length > 1) {
                    myContracts = myContracts.filter(function (c) { return validContractIds.includes(c.data.id); });
                    this.uiItems.resetAllNotSelected();
                    this.uiItems.makeSelectable(myContracts);
                    this.setClientState("client_playerDiscardContract", { descriptionmyturn: _("${you} must select a contract to discard") });
                }
                else if (selectedContract.length == 1 && myContracts.length <= 1) {
                    this.sendPickContract(selectedContract[0], null);
                }
            },

            playerTriggerOtherCityBonus: function (uiItem) {
                var cities = [];
                var cityBonus = this.uiItems.getByUiType("city_bonus");
                var tradingPostOnly = this.currentMoveArgs.trading_post_only;
                var offlimitCities = this.currentMoveArgs.offlimit_city_bonuses.split(',');

                for (var i = 0; i < cityBonus.length; i++) {
                    var c = cityBonus[i];
                    var hasTradingPost = this.uiItems.getPlayerUiItems("trading_post", "board", this.player_id).find(function (t) { return t.data.location_arg == c.data.location_arg });
                    var canSelect = (tradingPostOnly && hasTradingPost) || !tradingPostOnly;
                    canSelect = canSelect && offlimitCities.includes(c.data.type_arg) == false;
                    if (canSelect) {
                        var city = this.uiItems.getByUiType("map_node").find(function (m) { return parseInt(m.data.id) == parseInt(c.data.location_arg) });
                        cities.push(city);
                    }
                }
                this.uiItems.makeSelectable(cities);

                if (uiItem) {
                    var bonusUiItem = this.uiItems.getByUiType("city_bonus").find(function (b) { return parseInt(b.data.location_arg) == parseInt(uiItem.data.id) });
                    this.sendTriggerOtherCityBonus(bonusUiItem);
                }
            },

            playerMoveTradingPost: function (uiItem) {
                var items = this.uiItems.getPlayerUiItems("trading_post", "board", this.player_id);
                this.uiItems.resetAllSelectable();

                var mapNodeId = this.currentMoveArgs.map_node_id;
                var mapNode = this.uiItems.getByUiTypeAndId("map_node", mapNodeId).htmlNode;
                dojo.setStyle(mapNode, "border", "4px solid yellow");

                this.forceChangeUiItemsZIndex(items, 600);
                this.uiItems.makeSelectable(items);
                if (uiItem) { this.uiItems.toggleSelection(uiItem); }       //re-select
            },

            client_gift10PickBoardId: function (uiItem) {
                var boardIds = this.currentMoveArgs.boardIds;
                var mapNodes = this.uiItems.getByUiType("map_node").filter(function (m) { return boardIds.includes(m.data.id) });
                if (uiItem) {
                    this.sendFulfillGift(this.currentMoveArgs.giftId, uiItem.data.id);
                }
                this.makeSelectable(mapNodes);
            },

            client_playerDiscardContract: function (uiItem) {
                var selectedContract = this.uiItems.getSelectedItems();
                if (selectedContract.length > 1) {
                    var pickedContract = selectedContract.find(function (c) { return c.data.location == "board" });
                    var replacedContract = selectedContract.find(function (c) { return c.data.location == "hand" });
                    this.sendPickContract(pickedContract, replacedContract);
                }
                else if (uiItem.isSelected == false && uiItem.data.location == "board") {
                    this.onClickCancel();
                }
            },

            client_playerForceDiscardContract: function () {
                var playerId = this.player_id;
                var myContracts = this.uiItems.getByUiType("contract").filter(function (c) { return c.data.location == "hand" && c.data.location_arg == playerId; });
                var selectedContract = this.uiItems.getFirstSelectedItemByUiType("contract");
                this.uiItems.makeSelectable(myContracts);
                if (selectedContract) {
                    this.onClickChooseResource(selectedContract.data.id);
                }
            },

            client_playerForceDiscardGift: function (uiItem) {
                var giftIds = this.currentMoveArgs.giftIds;
                var gifts = this.uiItems.getByUiType("gift").filter(function (g) { return giftIds.includes(g.data.id); });

                var keepGiftItem = this.uiItems.getByUiType("1x_gift").find(function (i) { return i.data.location == "player_mat" });
                if (keepGiftItem) { gifts.push(keepGiftItem); }
                this.uiItems.makeSelectable(gifts);
                if (uiItem && uiItem.uiType == "gift") {
                    this.onClickChooseResource(uiItem.data.id);
                }
                else if (uiItem && uiItem.uiType == "1x_gift") // Fratre Nicolao -- pick 2 gifts piece
                {
                    this.confirmationDialog(_('Are you sure you want to use this piece?  This move cannot be undone'),
                        dojo.hitch(this, function () { this.onClickUsePlayerPiece(uiItem.data.id); }),
                        dojo.hitch(this, function () {
                            this.uiItems.toggleSelection(uiItem);
                            this.client_playerForceDiscardGift(null);
                        })
                    );
                }
            },

            transitionPlayerBonusAction: function (action) {
                if (this.isCurrentPlayerActive() && action) {
                    var actionType = action.type;
                    switch (actionType) {
                        case "trigger_other_city_bonus":
                            this.setClientState("playerTriggerOtherCityBonus", { descriptionmyturn: _("${you} must select another city bonus"), args: { offlimit_city_bonuses: action.type_arg, trading_post_only: false } });
                            break;
                        default:
                            this.setClientState("playerChooseResource", { descriptionmyturn: _("${you} must choose resources"), args: { action: action } });
                            break;
                    }
                }
            },

            transitionPlayerChooseResource: function (action) {
                if (this.isCurrentPlayerActive() && action) {
                    var actionType = action.type;
                    switch (actionType) {
                        case "discard_contract":
                            this.setClientState("client_playerForceDiscardContract", { descriptionmyturn: _("${you} must select a contract to discard") });
                            this.client_playerForceDiscardContract();       //todo odd?  why not rely on statemachine?
                            break;
                        case "discard_gift":
                            var selectableGiftIds = action.type_arg.split('_');
                            var discardGiftArgs = { "num_remaining": action.remaining_count, "giftIds": selectableGiftIds };
                            this.setClientState("client_playerForceDiscardGift", { descriptionmyturn: _("${you} must discard gifts (remaining: ${num_remaining})"), args: discardGiftArgs });
                            this.client_playerForceDiscardGift(null);        //todo odd?  why not rely on statemachine?
                            break;
                    }
                }
            },

            /* Highlight Player bonuses like Matteo Polo's extra contract */
            highlightBonuses: function (bonuses) {
                var items = [];
                var playerId = this.player_id;
                bonuses = bonuses.filter(function (b) { return b.player_id == playerId; });
                for (var i = 0; i < bonuses.length; i++) {
                    var bonus = bonuses[i];
                    if (bonus.bonus_type == "character" && bonus.bonus_type_arg == "contract")        //mateo polo
                    {
                        var spot = this.uiItems.getByUiType("character_spot").find(function (c) { return c.data.character_type == 7 });
                        spot.data.bonusId = bonus.id;
                        items.push(spot);
                    }
                    else if (bonus.bonus_type == "character" && bonus.bonus_type_arg == "pick_gift")     //Fratre Nicoalo
                    {
                        var spot = this.uiItems.getByUiType("character_spot").find(function (c) { return c.data.character_type == 8 });
                        spot.data.bonusId = bonus.id;
                        items.push(spot);
                    }
                    else if (bonus.bonus_type == "city_bonus") {
                        var spot = this.uiItems.getByUiType("city_bonus").find(function (c) { return c.data.type_arg == bonus.bonus_location });
                        spot = this.uiItems.getByUiTypeAndId("map_node", spot.data.location_arg);
                        spot.data.bonusId = bonus.id;
                        items.push(spot);
                    }
                }
                this.uiItems.makeSelectable(items);
            },

            showCharacterSelection: function (characterTypes) {
                var items = [];
                for (var i = 0; i < characterTypes.length; i++) {
                    var charType = characterTypes[i].character_type;
                    var charUiItem = this.uiItems.getByUiType("character").find(function (c) { return c.data.character_type == charType; });
                    if (charUiItem == null) {
                        charUiItem = this.uiItems.createAndAddItem("character", { player_id: "99", character_type: charType });
                        dojo.place(charUiItem.htmlNode, "characterSelection", "first");
                    }
                    items.push(charUiItem);
                }
                if (this.gamedatas.players[this.player_id]) {
                    this.uiItems.makeSelectable(items);
                }
                if (this.isCurrentPlayerActive()) {
                    dojo.setStyle("btnConfirm", "display", "none");
                }
            },

            transitionPlayerBonusState: function (pendingActions, pendingBonuses) {
                if (!this.isCurrentPlayerActive())          //not active player, nothing to do
                    return;

                var playerId = this.player_id;
                var myPendingAction = pendingActions.find(function (a) { return a.pending_player_id == playerId; });
                var myPendingBonuses = pendingBonuses.filter(function (b) { return b.player_id == playerId; });
                if (myPendingAction != null) {
                    this.uiItems.resetAllNotSelected();
                    this.transitionPlayerBonusAction(myPendingAction);
                }
                else if (myPendingBonuses) {
                    this.uiItems.resetAllSelectable();
                    this.highlightBonuses(myPendingBonuses);           //highlight bonuses available
                }
            },

            // onEnteringState: this method is called each time we are entering into a new game state.
            //                  You can use this method to perform some user interface changes at this moment.
            //
            onEnteringState: function (stateName, args) {
                console.log('Entering state: ' + stateName);
                this.currentMove = stateName;
                this.currentMoveArgs = args.args;
                switch (stateName) {
                    case 'pickCharacter':
                        dojo.setStyle("characterSelection", "display", "block");
                        this.showCharacterSelection(args.args.characters);
                        break;
                    case 'pickGoals':
                        dojo.setStyle("characterSelection", "display", "none");
                        this.showGoalSelection();
                        break;
                    case 'playerBonus':
                    case 'playerGunjBonus':
                        this.transitionPlayerBonusState(args.args.pending_actions, args.args.pending_bonuses);
                        break;
                    case 'playerTravel':
                        this.currentMoveArgs.selectedFigure = this.uiItems.getFirstSelectedItemByUiType("figure");
                        break;
                    case 'playerTurn':
                        this.mainActionAvailable = args.args.main_action_available;
                        this.canBuyBlackDie = args.args.can_buy_black_die;
                        this.canUsePersonalCityCard = args.args.can_use_personal_city_card;
                        if (!this.mainActionAvailable)          //swap sentences (bug #21345)
                        {
                            this.gamedatas.gamestate.descriptionmyturn = this.gamedatas.gamestate.descriptionmyturn_bonus;
                            this.gamedatas.gamestate.description = this.gamedatas.gamestate.description_bonus;
                            this.updatePageTitle();
                        }
                        break;
                    case 'playerChooseResource':
                        this.uiItems.resetAllSelectable();
                        this.transitionPlayerChooseResource(this.currentMoveArgs.action);
                        break;
                    case 'playerChooseCityCardAward':
                        this.uiItems.resetAllSelectable();
                        break;
                    case 'playerTriggerOtherCityBonus':
                        this.uiItems.resetAllSelectable();
                        break;
                    case 'client_changeDie':
                        this.uiItems.resetAllNotSelected();
                        break;
                    case 'dummmy':
                        break;
                }

                if (this.isCurrentPlayerActive() && ["playerTurn", "playerPickContract", "playerTravel", "playerTriggerOtherCityBonus", "playerMoveTradingPost"].includes(stateName)) {
                    this.uiItems.resetAllSelectable();
                    this[stateName]();
                }
                else if (!this.isCurrentPlayerActive() && !["pickCharacter"].includes(stateName)) {
                    this.uiItems.resetAllSelectable();
                }
            },

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //
            onLeavingState: function (stateName) {
                console.log('Leaving state: ' + stateName);
                switch (stateName) {
                    case 'rollAllDice':
                        this.uiItems.resetAllSelectable();
                        break;
                    case 'pickGoals':
                        dojo.empty("goalSelection");
                        break;
                }
            },

            // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
            //                        action status bar (ie: the HTML links in the status bar).
            //
            onUpdateActionButtons: function (stateName, args) {
                console.log('onUpdateActionButtons: ' + stateName);
                if (this.isCurrentPlayerActive()) {
                    switch (stateName) {
                        case 'pickCharacter':
                            this.addActionButton('btnConfirm', _('Confirm'), 'onClickConfirmCharacter');
                            break;
                        case 'pickGoals':
                            this.addActionButton('btnConfirm', _('Confirm'), 'onClickConfirmGoals');
                            break;
                        case 'playerDieCompensation':
                            var amount = args.compensation_amount[this.player_id];
                            for (var i = 0; i < amount + 1; i++) {
                                this.addActionButton('btnCompensation_camel_' + i, this.getHtmlForActionButtons({ camel: amount - i, coin: i }),
                                    dojo.hitch(this, dojo.partial(this.onClickPickCompensation, amount - i, i)));
                            }
                            break;
                        case 'playerTurn':
                            var passText = args.only_remaining_player ? _('Pass to main action') : _('Pass');
                            this.addActionButton('btnRerollDie', _('Re-roll die for 1 ') + this.getHtmlForActionButtons({ camel: 1 }), 'onClickRerollDie');
                            this.addActionButton('btnBumpUpDie', _('+1 to die for ') + this.getHtmlForActionButtons({ camel: 2 }), dojo.hitch(this, function () { this.onClickBumpDie("up") }));
                            this.addActionButton('btnBumpDownDie', _('-1 to die for ') + this.getHtmlForActionButtons({ camel: 2 }), dojo.hitch(this, function () { this.onClickBumpDie("down") }));
                            this.addActionButton('btnBuyBlackDie', _('Buy black die for ') + this.getHtmlForActionButtons({ camel: 3 }), 'onClickBuyBlackDie');
                            this.addActionButton('btnChangeDie', _('Change die value'), 'onClickChangeDie');
                            this.addActionButton('btnCancel', _('Cancel'), 'onClickCancel', null, false, "red");
                            this.addActionButton('btnPass', passText, 'onClickPass');
                            if (this.blinkPassHandle) { clearTimeout(this.blinkPassHandle); }
                            this.blinkPassHandle = setTimeout(function () {
                                var button = dojo.byId("btnPass");
                                if (button != null) { dojo.addClass(button, "blinking") }
                            }, 20000);
                            break;
                        case 'playerChooseResource':
                        case 'playerBonus':
                            var pendingAction = args.action;
                            if (pendingAction) {
                                switch (pendingAction.type) {
                                    case "camel_coin":
                                        var num = parseInt(pendingAction.remaining_count);
                                        this.addActionButton('btnCoin', this.getHtmlForActionButtons({ coin: num }), dojo.hitch(this, function () { this.onClickChooseResource('coin') }));
                                        this.addActionButton('btnCamel', this.getHtmlForActionButtons({ camel: num }), dojo.hitch(this, function () { this.onClickChooseResource('camel') }));
                                        break;
                                    case "choice_of_good":
                                        if (pendingAction.type_arg == "" || (pendingAction.type_arg == "pay" && this.hasResources({ "pepper": 1 }, this.player_id)))
                                            this.addActionButton('btnPepper', this.getHtmlForActionButtons({ pepper: 1 }), dojo.hitch(this, function () { this.onClickChooseResource('pepper') }));
                                        if (pendingAction.type_arg == "" || (pendingAction.type_arg == "pay" && this.hasResources({ "silk": 1 }, this.player_id)))
                                            this.addActionButton('btnSilk', this.getHtmlForActionButtons({ silk: 1 }), dojo.hitch(this, function () { this.onClickChooseResource('silk') }));
                                        if (pendingAction.type_arg == "" || (pendingAction.type_arg == "pay" && this.hasResources({ "gold": 1 }, this.player_id)))
                                            this.addActionButton('btnGold', this.getHtmlForActionButtons({ gold: 1 }), dojo.hitch(this, function () { this.onClickChooseResource('gold') }));
                                        break;
                                    case "2_diff_goods":
                                        if (pendingAction.type_arg == "" || (pendingAction.type_arg == "pay" && this.hasResources({ "pepper": 1, "silk": 1 }, this.player_id)))
                                            this.addActionButton('btnPepperSilk', this.getHtmlForActionButtons({ pepper: 1, silk: 1 }), dojo.hitch(this, function () { this.onClickChooseResource('pepper_silk') }));
                                        if (pendingAction.type_arg == "" || (pendingAction.type_arg == "pay" && this.hasResources({ "pepper": 1, "gold": 1 }, this.player_id)))
                                            this.addActionButton('btnSilk', this.getHtmlForActionButtons({ pepper: 1, gold: 1 }), dojo.hitch(this, function () { this.onClickChooseResource('pepper_gold') }));
                                        if (pendingAction.type_arg == "" || (pendingAction.type_arg == "pay" && this.hasResources({ "silk": 1, "gold": 1 }, this.player_id)))
                                            this.addActionButton('btnGold', this.getHtmlForActionButtons({ silk: 1, gold: 1 }), dojo.hitch(this, function () { this.onClickChooseResource('silk_gold') }));
                                        break;
                                    case "blackdie_or_3coins":
                                        if (pendingAction.type_arg != "no_black_die")
                                            this.addActionButton('btnBlackDie', _('Black Die'), dojo.hitch(this, function () { this.onClickChooseResource('black_die') }));
                                        this.addActionButton('btnCoin', this.getHtmlForActionButtons({ coin: 3 }), dojo.hitch(this, function () { this.onClickChooseResource('coin') }));
                                        break;
                                }
                            }
                            break;
                        case 'playerChooseCityCardAward':
                        case 'client_playerChooseCityCardAward':
                            var cardTypeData = this.gamedatas.material.city_cards[args.card_type];
                            if (cardTypeData.type == 30) {
                                var goods = this.getTwoDiffGoods(this.player_id);
                                for (var i = 0; i < goods.length; i++) {
                                    this.addActionButton('btnActivate_' + i, this.getHtmlForActionButtons(goods[i].cost), dojo.hitch(this, dojo.partial(this.onClickActivateMultipleCityCard, 1, goods[i].payload)));;
                                }
                            }
                            else if (args.isMultipleViaChoice) {
                                for (var i = 0; i < args.num_remaining; i++) {
                                    this.addActionButton('btnNumTimes_' + i, i + 1, dojo.hitch(this, dojo.partial(this.onClickActivateMultipleCityCard, i + 1, args.choiceIndex)));
                                }
                            }
                            else if (cardTypeData.kind == "choice") {
                                for (var i = 0; i < cardTypeData.choice.length; i++) {
                                    var choice = cardTypeData.choice[i];
                                    var label = this.getHtmlForActionButtons(choice.cost) + " → " + this.getHtmlForActionButtons(choice.award);
                                    this.addActionButton('btnActivate_' + i, label, dojo.hitch(this, dojo.partial(this.onClickActivateChoiceCityCard, cardTypeData.type, i, args.num_remaining)));
                                }
                            }
                            else if (cardTypeData.kind == "multiple") {
                                for (var i = 0; i < args.num_remaining; i++) {
                                    this.addActionButton('btnNumTimes_' + i, i + 1, dojo.hitch(this, dojo.partial(this.onClickActivateMultipleCityCard, i + 1, null)));
                                }
                            }
                            else if (cardTypeData.kind == "exchange") {
                                var labelCostToAward = this.getHtmlForActionButtons(cardTypeData.cost) + " → " + this.getHtmlForActionButtons(cardTypeData.award);
                                var labelAwardToCost = this.getHtmlForActionButtons(cardTypeData.award) + " → " + this.getHtmlForActionButtons(cardTypeData.cost);
                                if (this.hasResources(cardTypeData.cost, this.player_id) || cardTypeData.type == 19)       //allow vp to go negative
                                    this.addActionButton('btnCostToAward', labelCostToAward, dojo.hitch(this, dojo.partial(this.onClickActivateExchangeCityCard, "cost_to_award")));

                                if (this.hasResources(cardTypeData.award, this.player_id))
                                    this.addActionButton('btnAwardToCost', labelAwardToCost, dojo.hitch(this, dojo.partial(this.onClickActivateExchangeCityCard, "award_to_cost")));
                            }
                            if (args.can_skip) {
                                this.addActionButton('btnSkip', _("Skip"), 'onClickSkipCityAward', null, false, "red");
                            }
                            break;
                        case 'playerPickContract':
                            if (args.can_skip)
                                this.addActionButton('btnSkipContract', _("Skip"), 'onClickSkipPickContract', null, false, "red");
                            break;
                        case 'client_playerDiscardContract':
                            this.addActionButton('btnCancel', _("Cancel"), 'onClickCancel', null, false, "red");
                            break;
                        case 'playerTravel':
                            this.addActionButton('btnSkipTravel', _("Skip"), 'onClickSkipTravel', null, false, "red");
                            break;
                        case 'client_changeDie':
                            this.addActionButton('btnChangeDie1', 1, dojo.hitch(this, function () { this.onClickChangeDieValue(1) }));
                            this.addActionButton('btnChangeDie2', 2, dojo.hitch(this, function () { this.onClickChangeDieValue(2) }));
                            this.addActionButton('btnChangeDie3', 3, dojo.hitch(this, function () { this.onClickChangeDieValue(3) }));
                            this.addActionButton('btnChangeDie4', 4, dojo.hitch(this, function () { this.onClickChangeDieValue(4) }));
                            this.addActionButton('btnChangeDie5', 5, dojo.hitch(this, function () { this.onClickChangeDieValue(5) }));
                            this.addActionButton('btnChangeDie6', 6, dojo.hitch(this, function () { this.onClickChangeDieValue(6) }));
                            this.addActionButton('btnCancel', _('Cancel'), 'onClickCancel', null, false, "red");
                            break;
                        case 'playerTriggerOtherCityBonus':
                            this.addActionButton('btnSkipTriggerOtherCityBonus', _("Skip"), 'onClickSkipTriggerOtherCityBonus', null, false, "red");
                            break;
                        case 'playerMoveTradingPost':
                            this.addActionButton('btnConfirmMoveTradingPost', _("Confirm"), 'onClickConfirmMoveTradingPost');
                            this.addActionButton('btnSkipMoveTradingPost', _("Skip"), 'onClickSkipMoveTradingPost', null, false, "red");
                            break;
                    }

                    if (args && args.can_undo && args.can_undo == 1) {
                        this.canUndo = true;
                        this.addActionButton('btnUndo', _('Undo'), 'onClickUndo', null, false, "gray");
                    }
                    else {
                        this.canUndo = false;
                    }
                }
                if (stateName != "gameEnd")
                    this.addActionButton('btnPlayerAid', _('Player Aid'), 'onClickPlayerAid', null, false, 'gray');
            },

            ///////////////////////////////////////////////////
            //// Utility methods
            /*

                Here, you can defines some utility methods that you can use everywhere in your javascript
                script.
            */
            onScreenWidthChange: function () {
                if (this.uiItems.length > 0) { this.repositionPlayerMats(); }
            },

            getColourNameForPlayerId: function (playerId) {
                var colourMap = { "ff0000": "red", "008000": "green", "0000ff": "blue", "ffa500": "yellow", "663399": "purple" };
                if (this.gamedatas.players[playerId] != undefined)
                    return colourMap[this.gamedatas.players[playerId].color];

                return "green";
            },

            getUnusedColorName: function () {
                var possibleColors = ["red", "green", "blue", "yellow", "purple"];
                for (var playerId in this.gamedatas.players) {
                    possibleColors.splice(possibleColors.indexOf(this.getColourNameForPlayerId(playerId)), 1);
                }
                return possibleColors[0];
            },

            format_string_recursive: function (log, args) {
                try {
                    if (log && args && !args.processed) {
                        args.processed = true;
                        if (!this.isSpectator)
                            args.You = this.divYou(); // will replace ${You} with colored version

                        var keys = ["resource_type", "camel", "gift_type"];
                        for (var i in keys) {
                            var key = keys[i];
                            if (typeof args[key] == 'string') {
                                args[key] = this.getPieceForLog(key, args);
                            }
                        }
                    }
                }
                catch (e) {
                    console.error(log, args, "Exception thrown", e.stack);
                }
                return this.inherited(arguments);
            },

            divYou: function () {
                var color = this.gamedatas.players[this.player_id].color;
                var color_bg = "";
                if (this.gamedatas.players[this.player_id] && this.gamedatas.players[this.player_id].color_back) {
                    color_bg = "background-color:#" + this.gamedatas.players[this.player_id].color_back + ";";
                }
                var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + __("lang_mainsite", "You") + "</span>";
                return you;
            },

            getPieceForLog: function (key, args) {
                var htmlLog = "";
                if (key == "resource_type") {
                    var resources = {};
                    resources[args.resource_type] = 1;
                    htmlLog = this.getHtmlForActionButtons(resources);
                }
                else if (key == "gift_type") {
                    var backgroundCoords = this.uiItems.getBackgroundPosition("gift", args.gift_type);
                    var giftTitle = "gift " + args.gift_type;
                    backgroundPosition = "background-position:" + backgroundCoords.x + "px " + backgroundCoords.y + "px;";
                    htmlLog = '<span class="gift log" title="' + giftTitle + '" style="' + backgroundPosition + ' "></span>';
                }
                return htmlLog;
            },

            getHtmlForActionButtons: function (resources) {
                var totalAmount = 0;
                var html = '';

                for (var resourceType in resources) {
                    totalAmount += resources[resourceType];
                }

                for (var resourceType in resources) {
                    var amount = resources[resourceType];
                    if (amount > 0) {
                        if (resourceType == "choice_of_good") {
                            html += '<span class="small_piece small_pepper"></span> / <span class="small_piece small_silk"></span> / <span class="small_piece small_gold"></span>';
                        }
                        else {
                            if (amount > 1 || (Object.keys(resources).length > 1 && totalAmount != Object.keys(resources).length)) {
                                html += amount + ' ';
                            }
                            html += ' <span class="small_piece small_' + resourceType + '" title="' + resourceType + '"></span>  ';
                        }
                    }
                }
                return html;
            },

            updateResourceInfo: function (playerId) {
                var resources = this.playerResources[playerId];
                for (var resourceType in resources) {
                    dojo.setAttr($('resources-' + resourceType + '-' + playerId), 'innerHTML', resources[resourceType]);
                    dojo.setAttr($("panel_value_" + resourceType + "_" + playerId), "innerHTML", resources[resourceType]);
                }
                var postsRemaining = this.uiItems.getByUiType("trading_post").filter(function (p) { return p.data.location == "player_mat" && p.data.player_id == playerId });
                var postsPlaced = this.uiItems.getByUiType("trading_post").filter(function (p) { return p.data.location != "player_mat" && p.data.player_id == playerId });
                dojo.setAttr($("trading-posts-left-" + playerId), "innerHTML", postsPlaced.length + " + " + postsRemaining.length);
                if (this.scoreCtrl[playerId]) { this.scoreCtrl[playerId].setValue(resources["vp"]); }
            },

            updateHourGlass: function (playerId) {
                dojo.query('.panel_hourglass').style("display", "none");
                dojo.setStyle($('panel_hourglass_' + playerId), "display", "inline");
            },

            updateCurrentRound: function (roundNumber) {
                roundNumber = parseInt(roundNumber);
                for (var i = 0; i < roundNumber; i++) {
                    dojo.setStyle($("round_" + (i + 1)), "display", "none");
                }
                dojo.setAttr("roundNumber", "innerHTML", roundNumber);
            },

            getCharacterItemsDescription: function (characterType) {
                var description = dojo.create("div");
                if (characterType == 9)     //Khan Arghun
                {
                    var items = this.uiItems.getByUiType("city_card").filter(function (c) { return c.data.location == "pick_character" });
                    for (var i = 0; i < items.length; i++) {
                        dojo.setStyle(items[i].htmlNode, "display", "inline-block");
                        dojo.setStyle(items[i].htmlNode, "transform", "scale(1)");
                        dojo.setStyle(items[i].htmlNode, "position", "static");
                        description.append(items[i].htmlNode);
                    }
                }
                return description;
            },

            getAwardSpots: function (selectedPlace, selectedDice) {
                var awardSpots = [];
                var place = selectedPlace.data.place;
                if (place == "bazaar" || place == "travel") {
                    var placeIndex = selectedPlace.data.index;
                    var lowestDie = Math.min.apply(Math, selectedDice.map(function (d) { return parseInt(d.data.value) }));

                    awardSpots = this.uiItems.filter(function (u) {
                        return u.uiType == "award_spot" && u.data.place == place && u.data.tied_to_index == placeIndex && lowestDie >= u.data.min_die
                    });

                    if (place == "bazaar" && placeIndex == 3)       //gold
                    {
                        if (lowestDie == 1 || lowestDie == 2)       //only 1 & 2 auto-select otherwise give all options.
                        {
                            awardSpots = [awardSpots[awardSpots.length - 1]];
                        }
                        else {
                            awardSpots = awardSpots.filter(function (a) { return a.data.index > 1; });
                        }
                    }
                    else if (place == "bazaar" && (placeIndex == 0 || (lowestDie != 4 && lowestDie != 6))) {
                        awardSpots = [awardSpots[awardSpots.length - 1]];     //make a new array of just last element
                    }
                    else if (place == "bazaar" && (lowestDie == 4 || lowestDie == 6)) {
                        awardSpots = [awardSpots[awardSpots.length - 2], awardSpots[awardSpots.length - 1]];
                    }
                }
                return awardSpots;
            },

            isMapNodeAvailable: function (currentMapNode, futureMapNode) {
                var futureMapNodeId = futureMapNode.data.id;
                var currentMapNodeId = currentMapNode.data.id;
                var validEdges = [];

                for (var i = 0; i < this.gamedatas.material.edges.length; i++) {
                    var edge = this.gamedatas.material.edges[i];
                    if ((edge.src == futureMapNodeId && edge.dst == currentMapNodeId) || (edge.src == currentMapNodeId && edge.dst == futureMapNodeId)) {
                        if (this.hasResources(edge.cost, this.player_id)) {
                            validEdges.push(edge);
                        }
                    }
                    else if (this.myCharacterType == 3 && this.gamedatas.material.board[currentMapNodeId].type == "oasis" && currentMapNodeId != futureMapNodeId
                        && this.gamedatas.material.board[futureMapNodeId].type == "oasis")      //3 = Johannes Carprini
                    {
                        validEdges.push(edge);
                    }
                }
                return validEdges.length > 0;
            },

            getValidBoardIdsFiguresAreOn: function (playerId) {
                var figures = this.uiItems.getByUiType("figure").filter(function (f) { return f.data.player_id == playerId; })
                var tradingPosts = this.uiItems.getPlayerUiItems("trading_post", "board", this.player_id);
                var boardIds = [];
                dojo.forEach(figures, function (f) {
                    var hasTradingPostOnSpot = tradingPosts.find(function (t) { return t.data.location_arg == f.data.location_arg; });
                    if (this.gamedatas.material.board[f.data.location_arg].type.endsWith("city") && hasTradingPostOnSpot == null) {
                        boardIds.push(f.data.location_arg);
                    }
                }.bind(this));
                return boardIds;
            },

            getDiceOnSpot: function (location, locationArg) {
                return this.uiItems.getByUiType("die").filter(function (d) { return d.data.location == location && d.data.location_arg == locationArg });
            },

            getDiceOnUiItem: function (uiItem) {
                var dice;
                if (uiItem.uiType == "board_spot" || uiItem.uiType == "character_spot") {
                    dice = this.getDiceOnSpot(uiItem.data.place, uiItem.data.index);
                }
                else if (uiItem.uiType == "city_card") {
                    dice = this.getDiceOnSpot("city_card", uiItem.data.type_arg);
                }
                return dice;
            },

            isPlayerDiceOnUiItem: function (uiItem, playerId) {
                var dice = this.getDiceOnUiItem(uiItem);
                return dice.filter(function (d) { return d.data.type == "regular" && d.data.player_id == playerId }).length > 0;
            },

            isPlayerDiceOnBoardSpotArea: function (spotArea, playerId)     //when we want to treat multiple board spots as one "area" (i.e. khan)
            {
                var dice = this.uiItems.filter(function (d) {
                    return d.uiType == "die" && d.data.type == "regular" && d.data.player_id == playerId
                        && d.data.location == spotArea
                });
                return dice.length > 0;
            },

            getPlaceDataByUiItem: function (uiItem) {
                var data;
                if (uiItem.uiType == "board_spot") {
                    data = this.gamedatas.material["spots"].find(function (b) { return b.place == uiItem.data.place && b.index == uiItem.data.index && !b.is_award_spot });
                }
                else if (uiItem.uiType == "award_spot") {
                    data = this.gamedatas.material["spots"].find(function (b) { return b.place == uiItem.data.place && b.index == uiItem.data.index && b.is_award_spot });
                }
                else if (uiItem.uiType == "city_card") {
                    data = this.gamedatas.material["city_cards"].find(function (c) { return c.type == uiItem.data.type_arg });
                }
                return data;
            },

            isPlaceSpotAvailable: function (uiItem, selectedDice, playerId) {
                var usingRegularDice = selectedDice.filter(function (d) { return d.data.type == "regular"; }).length > 0;
                var isPlayerDiceOnSpot = usingRegularDice && this.isPlayerDiceOnUiItem(uiItem, playerId);
                var boardMaterial = this.getPlaceDataByUiItem(uiItem);
                var isAvailable = boardMaterial.num_dice == selectedDice.length;

                if (boardMaterial["allow_multiple"] == false)       //only one player can go here
                    isAvailable &= this.getDiceOnUiItem(uiItem).length == 0;

                if (uiItem.uiType == "board_spot" && uiItem.data.place == "khan") {
                    isAvailable &= this.getDiceOnSpot("khan", uiItem.data.index).length == 0;
                    if (isAvailable && uiItem.data.index > 0) {
                        var diceOnSpot = this.getDiceOnSpot("khan", uiItem.data.index - 1);
                        isAvailable &= diceOnSpot.length == 1 && diceOnSpot[0].data.value <= selectedDice[0].data.value;     //assume only one die in selectedDice
                    }
                    isAvailable &= !this.isPlayerDiceOnBoardSpotArea("khan", playerId) || !usingRegularDice;        //can only go here if not using regular dice or not on board spot
                }
                else if (uiItem.uiType == "board_spot" && uiItem.data.place != "coin3") {
                    isAvailable &= !isPlayerDiceOnSpot;     //cannot play at the same spot unless coin3
                }
                else if (uiItem.uiType == "city_card")      //make sure trading post is in the city containing the card
                {
                    isAvailable &= this.uiItems.getPlayerUiItems("trading_post", "board", playerId).filter(function (t) { return t.data.location_arg == uiItem.data.location_arg }).length > 0;
                }

                return isAvailable;
            },

            hasResources: function (cost, playerId) {
                var isValid = true;
                for (var resourceType in cost) {
                    if (resourceType == "choice_of_good") {
                        isValid = this.playerResources[playerId]["pepper"] > 0 || this.playerResources[playerId]["silk"] > 0 || this.playerResources[playerId]["gold"] > 0;
                    }
                    else if (cost[resourceType] > this.playerResources[playerId][resourceType]) {
                        isValid = false;
                    }
                }
                return isValid;
            },

            getTwoDiffGoods: function (playerId) {
                var result = [];
                if (this.playerResources[playerId]["silk"] > 0 && this.playerResources[playerId]["pepper"] > 0) {
                    result.push({ cost: { pepper: 1, silk: 1 }, "payload": "pepper_silk" });
                }
                if (this.playerResources[playerId]["pepper"] > 0 && this.playerResources[playerId]["gold"] > 0) {
                    result.push({ cost: { pepper: 1, gold: 1 }, "payload": "pepper_gold" });
                }
                if (this.playerResources[playerId]["silk"] > 0 && this.playerResources[playerId]["gold"] > 0) {
                    result.push({ cost: { silk: 1, gold: 1 }, "payload": "silk_gold" });
                }

                return result;
            },

            updatePlayerTurnButtons: function (selectedDice) {
                dojo.forEach(['btnCancel', 'btnPass', 'btnRerollDie', 'btnBumpUpDie', 'btnBumpDownDie', 'btnChangeDie', 'btnBuyBlackDie'], function (id) {       //hide all first
                    dojo.setStyle($(id), "display", "none");
                });

                if (this.myCharacterType != 2) {
                    var die = selectedDice.length == 1 ? selectedDice[0] : null;
                    if (die && die.data.value != 6)
                        dojo.setStyle("btnBumpUpDie", "display", "inline-block");

                    if (die && die.data.value != 1)
                        dojo.setStyle("btnBumpDownDie", "display", "inline-block");

                    if (die)
                        dojo.setStyle("btnRerollDie", "display", "inline-block");
                }
                else if (this.myCharacterType == 2 && selectedDice.length > 0) {
                    dojo.setStyle("btnChangeDie", "display", "inline-block");
                }

                if (this.mainActionAvailable == 0 && this.uiItems.getSelectedItems().length == 0) {
                    dojo.setStyle("btnPass", "display", "inline-block");
                }
                else if (this.mainActionAvailable == 0) {
                    dojo.setStyle("btnPass", "display", "none");
                }

                if (this.uiItems.getSelectedItems().length > 0) {
                    dojo.setStyle("btnCancel", "display", "inline-block");
                    if (this.canUndo) { dojo.setStyle("btnUndo", "display", "none"); }
                }
                else if (this.canUndo) {
                    dojo.setStyle("btnUndo", "display", "inline-block");
                }

                if (this.canBuyBlackDie && selectedDice.length == 0) {
                    dojo.setStyle("btnBuyBlackDie", "display", "inline-block");
                }
            },

            getSelectableCharacterSpotUIItemsForPlayerTurnState: function (selectedDice, playerId) {
                /**
                * Function that gets places where a player can use their die on their character card.
                * Used primarlyby Gunj Kököchin (characterType == 11)
                * @param {Array} selectedDice - Array that contains the dice the player selected
                * @param {number} playerId - The playerId of the player using the action
                * @return {Array} - List of uItems that represent available spots on the character card
                */
                var spots = [];
                if (this.player_id == playerId && this.myCharacterType == 11 && selectedDice.length == 1) {
                    spots = this.uiItems.getByUiType("character_spot").filter(c => c.data.player_id == playerId);
                    spots = spots.filter((uiItem) => !this.getDiceOnUiItem(uiItem).length);
                }
                return spots;
            },

            getSelectableGiftUIItemsForPlayerTurnState: function (selectedDice, playerId) {
                let validGiftTypes = [];
                const giftItemsOnPlayerHand = this.uiItems.getByUiType("gift").filter(function (g) { return g.data.location_arg == playerId; });
                if (selectedDice.length > 0) {
                    validGiftTypes = [7]; // no extra cost
                    if (selectedDice.length == 1) {
                        validGiftTypes.push(4); // change die value
                    }
                }
                else if (selectedDice.length == 0) {
                    validGiftTypes = [1, 2, 14];
                }
                return giftItemsOnPlayerHand.filter(function (g) { return validGiftTypes.includes(parseInt(g.data.type_arg)) });
            },

            getSelectableCityCardsUIItemsForPlayerTurnState: function (playerId) {
                if (this.player_id == playerId && this.canUsePersonalCityCard == true && this.myCharacterType == 9) // 9 = Khan
                    return this.uiItems.getByUiType("city_card").filter(g => g.data.location == 'player_mat');
                return [];
            },

            getFulfillableContracts: function (playerId) {
                var validContracts = [];
                var contracts = this.uiItems.getPlayerUiItems("contract", "hand", playerId);
                for (var i = 0; i < contracts.length; i++) {
                    var contractCost = this.gamedatas.material.contracts[contracts[i].data.type_arg].cost;
                    if (this.hasResources(contractCost, playerId)) {
                        validContracts.push(contracts[i]);
                    }
                }
                return validContracts;
            },

            ///////////////////////////////////////////////////
            //// Drawing & Animation methods
            createShakeDieAnimation: function (dieNode) {
                var shakeDieAnimation = [];
                var nodeStyle = dojo.getComputedStyle(dieNode);
                var curTop = parseInt(nodeStyle.top.replace('px', ''));
                var curLeft = parseInt(nodeStyle.left.replace('px', ''));
                var pipNode = dojo.query(".die_pip:first-child", dieNode)[0];

                for (var i = 0; i < 10; i++)     //10 shakes
                {
                    var randTop = Math.floor(Math.random() * 10) - 5;
                    var randLeft = Math.floor(Math.random() * 10) - 5;
                    var randPip = Math.floor(Math.random() * 6);
                    var anim = dojo.animateProperty({
                        node: dieNode,
                        properties: {
                            top: { start: curTop + randTop, end: curTop },
                            left: { start: curLeft + randLeft, end: curLeft },
                        },
                        duration: 50
                    });
                    var pipAnim = dojo.animateProperty({
                        node: pipNode,
                        properties: {
                            "background-position-y": { start: -100 * randPip, end: -100 * randPip }
                        },
                        duration: 50
                    });
                    var shakeDiePipe = dojo.fx.combine([anim, pipAnim]);
                    shakeDieAnimation.push(shakeDiePipe);
                }
                return dojo.fx.chain(shakeDieAnimation);
            },

            setShakeDiceResult: function (dice) {
                for (var i = 0; i < dice.length; i++) {
                    var die = dice[i];
                    this.setUiPositionForDiceOnBoard(die.data.location, die.data.location_arg, die.data.location_height, die.data.player_id);
                    this.uiItems.setBackgroundUiItem(die);
                }
                this.repositionPlayerMats();
            },

            shakeDice: function (dice) {
                var allDiceShakes = [];
                var triggerCombineOnEndCounter = 0;
                for (var i = 0; i < dice.length; i++) {
                    var die = dice[i];
                    if (die.data.after_shake_value != undefined) {
                        var dieAnim = this.createShakeDieAnimation(die.htmlNode);
                        dieAnim.onEnd = function () {
                            triggerCombineOnEndCounter -= 1;
                            if (triggerCombineOnEndCounter == 0) {
                                this.setShakeDiceResult(dice);
                            }
                        }.bind(this);
                        die.data.value = die.data.after_shake_value;
                        die.uiPosition = 0;
                        delete die.data.after_shake_value;
                        triggerCombineOnEndCounter += 1
                        allDiceShakes.push(dieAnim);
                    }
                }
                if (allDiceShakes.length > 0) {
                    //onEnd fires before inner animations for some reason, cannot rely on it (dojo bug?  https://bugs.dojotoolkit.org/ticket/16305)
                    var allDiceAnimation = dojo.fx.combine(allDiceShakes);
                    allDiceAnimation.play();
                }
            },

            createShakeDiceClosure: function (dice) {
                return function () {
                    this.shakeDice(dice);
                }.bind(this);
            },

            getMapNodeMarginBox: function (locationArg) {
                var mapNode = $("map_node_" + locationArg);
                var result = dojo.marginBox(mapNode);
                return result;
            },

            getPositionForUiItem: function (uiItem) {
                var position = { top: null, left: null };

                if (uiItem.uiType == "character_spot") {
                    position.left = -120;
                    if (uiItem.data.character_type == 7)    //Matteo Polo
                    {
                        position.top = -68;
                    }
                    else if (uiItem.data.character_type == 8) //Fratre Nicolao
                    {
                        position.top = -66;
                    }
                    else if (uiItem.data.character_type == 11 && uiItem.data.index == 0)      //Gunj Kököchin
                    {
                        position.top = -104;
                    }
                    else if (uiItem.data.character_type == 11 && uiItem.data.index == 1)      //Gunj Kököchin
                    {
                        position.top = -65;
                    }
                }
                else if (uiItem.uiType == "city_bonus") {
                    var mapNode = this.getMapNodeMarginBox(uiItem.data.location_arg);
                    position.top = mapNode.t + 4;
                    position.left = mapNode.l + 6;
                }
                else if (uiItem.uiType == "contract" && uiItem.data.location == "board") {
                    var mapNode = dojo.marginBox($("board_spot_contracts_0"));
                    position.top = mapNode.t;
                    position.left = mapNode.l + (uiItem.data.location_arg * 85) + 44;
                }
                else if (uiItem.uiType == "outpost") {
                    var mapNode = this.getMapNodeMarginBox(uiItem.data.location_arg);
                    position.top = mapNode.t + 16;
                    position.left = mapNode.l + 16;
                }
                else if (uiItem.uiType == "city_card" && uiItem.data.location == "board") {
                    var mapNode = this.getMapNodeMarginBox(uiItem.data.location_arg);
                    if (uiItem.data.location_position == 0) {
                        position.top = mapNode.t + 63;
                        position.left = mapNode.l + 6;
                    }
                    else if (uiItem.data.location_position == 1) {
                        position.top = mapNode.t + 127;
                        position.left = mapNode.l - 44;
                    }
                    else if (uiItem.data.location_position == 2) {
                        position.top = mapNode.t + 127;
                        position.left = mapNode.l + 56;
                    }
                }
                else if (uiItem.uiType == "figure") {
                    var mapNode = this.getMapNodeMarginBox(uiItem.data.location_arg);
                    var boardType = this.gamedatas.material.board[parseInt(uiItem.data.location_arg)].type;
                    position.top = mapNode.t - 20;
                    position.left = mapNode.l - 6;
                    if (uiItem.uiPosition != undefined) {
                        if (boardType == "oasis" && uiItem.uiPosition > 1) {
                            position.left += (uiItem.uiPosition - 2) * 24;
                            position.top += 20;
                        }
                        else {
                            position.left += uiItem.uiPosition * 24;
                        }
                    }
                }
                else if (uiItem.uiType == "trading_post" && !uiItem.data.location.endsWith("mat")) {
                    var boardType = this.gamedatas.material.board[parseInt(uiItem.data.location_arg)].type;
                    var leftOffset = [5, 28, 54, 80, 40];       //default large_city
                    var topOffset = [15, 6, 6, 15, 30];

                    if (boardType == "small_city") {
                        leftOffset = [5, 44, 5, 44, 22];
                        topOffset = [25, 25, 50, 50, 30];
                    }
                    else if (boardType == "bejing") {
                        leftOffset = [32, 70, 32, 8, 8];
                        topOffset = [15, 30, 46, 30, 66];
                    }

                    var mapNode = this.getMapNodeMarginBox(uiItem.data.location_arg);
                    position.top = mapNode.t + topOffset[uiItem.uiPosition];
                    position.left = mapNode.l + leftOffset[uiItem.uiPosition];
                }
                else if (uiItem.uiType == "die" && !uiItem.data.location.endsWith("mat")) {
                    var dest = this.getDestinationForBoardUiItem(uiItem);
                    var mapNode = dojo.marginBox(dest);

                    if (uiItem.data.location == "travel") {
                        position.top = mapNode.t + uiItem.uiPosition * this.uiItems.itemConfig["die"].height - 8;       //8px for travel spot offset
                        position.left = mapNode.l;
                    }
                    else if (uiItem.data.location == "city_card") {
                        position.top = mapNode.t + 14;
                        position.left = mapNode.l + 2;
                    }
                    else if (uiItem.data.location == "bazaar") {
                        position.top = mapNode.t;
                        position.left = mapNode.l + uiItem.uiPosition * (this.uiItems.itemConfig["die"].width - 4);
                    }
                    else if (uiItem.data.location == "gunj") {
                        position.left = -120;
                        position.top = uiItem.data.location_arg == 0 ? -104 : -64;
                    }
                    else {
                        position.top = mapNode.t;
                        position.left = mapNode.l + uiItem.uiPosition * this.uiItems.itemConfig["die"].width;
                    }

                    if (uiItem.data.location_height != undefined && uiItem.data.location_height > 0) {
                        position.top = position.top - (8 * uiItem.data.location_height);
                        position.left = position.left - (4 * uiItem.data.location_height);
                    }
                }

                return position;
            },

            positionUiItem: function (uiItem) {
                var position = this.getPositionForUiItem(uiItem);
                if (position.top != null && position.left != null) {
                    dojo.setStyle(uiItem.htmlNode, "top", position.top + "px");
                    dojo.setStyle(uiItem.htmlNode, "left", position.left + "px");
                }
            },

            sortUiItemsByWeightPosition: function (a, b) {
                var aValue = this.getWeight(a) + a.uiPosition;
                var bValue = this.getWeight(b) + b.uiPosition;
                return aValue - bValue;
            },

            placeOnNewParent: function (top, left, parent, node) {
                dojo.setStyle(node, "top", top + "px");
                dojo.setStyle(node, "left", left + "px");
                dojo.place(node, parent);
            },

            getPlayerItemsForPlayerMatContainer: function (itemContainer, playerId) {
                var items = this.uiItems.getByUiTypes(this.playerMatContainerUiTypes[itemContainer]).filter(function (d) {
                    return (d.data.location.endsWith("mat") && d.data.player_id == playerId) || (d.data.location.endsWith("hand") && d.data.location_arg == playerId)
                });
                items.sort(this.sortUiItemsByWeightPosition.bind(this.uiItems));
                return items;
            },

            getDestinationForBoardUiItem: function (uiItem) {
                if (uiItem.uiType == "die" && uiItem.data.location == "city_card") {
                    return this.uiItems.getByUiType("city_card").find(function (c) { return c.data.type_arg == uiItem.data.location_arg }).htmlNode;
                }
                else if (uiItem.uiType == "die" && uiItem.data.location == "gunj") {
                    return this.uiItems.getByUiType("character_spot").find(function (c) { return c.data.character_type == 11 && c.data.index == uiItem.data.location_arg }).htmlNode;
                }

                return "board_spot_" + uiItem.data.location + "_" + uiItem.data.location_arg;
            },

            buildBoardAnimationFromUiItem: function (uiItem) {
                var position = this.getPositionForUiItem(uiItem);
                var parentContainer = this.getParentContainerForUiItem(uiItem);
                var anim = this.slideToObjectPos(uiItem.htmlNode, parentContainer, position.left, position.top);
                anim.onEnd = dojo.partial(this.placeOnNewParent, position.top, position.left, parentContainer);
                return anim;
            },

            getContainerTypeByUiType: function (uiType) {
                for (var containerType in this.playerMatContainerUiTypes) {
                    if (this.playerMatContainerUiTypes[containerType].includes(uiType))
                        return containerType;
                }
                return null;
            },

            changeUiItemPointerEvents: function (uiItem) {
                if (uiItem.uiType == "die" && !uiItem.data.location.endsWith("mat")) {
                    dojo.setStyle(uiItem.htmlNode, "pointer-events", "none");
                }
                else if (uiItem.uiType == "die") {
                    dojo.setStyle(uiItem.htmlNode, "pointer-events", "auto");
                }
            },

            forceChangeUiItemsZIndex: function (uiItems, newZIndex) {
                for (var i = 0; i < uiItems.length; i++) {
                    dojo.setStyle(uiItems[i].htmlNode, "zIndex", newZIndex);
                }
            },

            resetUiItemsZIndex: function (uiItems) {
                for (var i = 0; i < uiItems.length; i++) {
                    this.changeUiItemZIndex(uiItems[i]);
                }
            },

            changeUiItemZIndex: function (uiItem) {
                var baseZIndex = this.uiItems.itemConfig[uiItem.uiType];
                baseZIndex = baseZIndex.zIndex != undefined ? baseZIndex.zIndex : 10;

                if (uiItem.uiType == "die") {
                    if (uiItem.data.location == "bazaar") {
                        baseZIndex += 100 - parseInt(uiItem.data.location_arg) * 10;
                    }

                    if (uiItem.data.location_height) {
                        baseZIndex += parseInt(uiItem.data.location_height);
                    }
                }
                else if (uiItem.uiType == "trading_post") {
                    baseZIndex += parseInt(uiItem.data.position);
                }
                dojo.setStyle(uiItem.htmlNode, "zIndex", baseZIndex);
            },

            onEndAnimateGoalCards: function (goalCardsUiItems) {
                return function () {
                    var container = this.getContainerTypeByUiType("goal_card");
                    for (var i = 0; i < goalCardsUiItems.length; i++) {
                        var uiItem = goalCardsUiItems[i];
                        uiItem.data.location = "goal_hand";
                        this.uiItems.setBackgroundUiItem(uiItem);
                        this.moveUiItemToParentContainer(uiItem, container + "-container-" + this.player_id);
                        dojo.setStyle(uiItem.htmlNode, "top", "0px");
                        dojo.setStyle(uiItem.htmlNode, "left", "0px");
                        this.uiItems.addTooltip(uiItem);
                    }
                    dojo.setStyle("goalSelection", "display", "none");
                    this.repositionPlayerMat(container, goalCardsUiItems, this.player_id);
                    if (this.prefs[100].value == 0) { this.showAllGoalAnchors(); }
                }.bind(this);
            },

            animateGoalCards: function (goalCardsUiItems) {
                var anims = [];
                var playerId = this.player_id;

                this.uiItems.resetSelectable(goalCardsUiItems);

                var backGoalCards = this.uiItems.getByUiType("goal_card").filter(function (g) { return g.data.type_arg == 29 && g.data.location_arg == playerId });  //delete fake goal card
                for (var i = 0; i < backGoalCards.length; i++) {
                    var backGoalCard = backGoalCards[i];
                    backGoalCard.data.location = "box";
                    this.fadeOutAndDestroy(backGoalCard.htmlNode, 500, 0);
                }

                for (var i = 0; i < goalCardsUiItems.length; i++) {
                    dojo.setStyle(goalCardsUiItems[i].htmlNode, "zIndex", 500);
                    var anim = this.slideToObject(goalCardsUiItems[i].htmlNode, "playerMat-" + playerId);
                    anims.push(anim);
                }
                var animChain = dojo.fx.chain(anims);
                animChain.onEnd = this.onEndAnimateGoalCards(goalCardsUiItems);
                animChain.play();
            },

            animateAndMoveUiItemsWithCallback: function (uiItems, onAnimateEndCallback) {
                var modifiedItems = {};
                var anims = [];
                var onAnimateEndCallbackCalled = false;
                for (var i = 0; i < uiItems.length; i++) {
                    var uiItem = uiItems[i];
                    var playerId = this.uiItems.getPlayerId(uiItem);
                    var containerType = this.getContainerTypeByUiType(uiItem.uiType);

                    if (playerId != null && modifiedItems[playerId] == undefined) { modifiedItems[playerId] = {}; }
                    if (playerId != null && containerType != null && modifiedItems[playerId][containerType] == undefined) { modifiedItems[playerId][containerType] = []; }

                    if (this.uiItems.isPlayerItem(uiItem))           //check moving to player mat or board?
                    {
                        modifiedItems[playerId][containerType].push(uiItem);
                    }
                    else {
                        anims.push(this.buildBoardAnimationFromUiItem(uiItem));
                    }
                    this.changeUiItemPointerEvents(uiItem);
                    this.changeUiItemZIndex(uiItem);
                }

                for (var playerId in modifiedItems)     //play player mat animations if any
                {
                    for (var containerType in modifiedItems[playerId]) {
                        this.repositionPlayerMatWithCallback(containerType, modifiedItems[playerId][containerType], onAnimateEndCallback, playerId);
                        onAnimateEndCallbackCalled = true;
                    }
                }

                if (anims.length) {
                    dojo.fx.combine(anims).play();
                }
                else if (!onAnimateEndCallbackCalled) {
                    onAnimateEndCallback();
                }
            },

            animateAndMoveUiItems: function (uiItems) {
                this.animateAndMoveUiItemsWithCallback(uiItems, function (fx) { });
            },

            repositionPlayerMatWithCallback: function (itemContainer, addedItems, onAnimateEndCallback, playerId)      //updates player mat with new/removed items
            {
                const items = this.getPlayerItemsForPlayerMatContainer(itemContainer, playerId);
                const container = itemContainer == "small" ? $("small-container-" + playerId) : $("large-container-" + playerId);
                var anims = [];
                var containerTop = itemContainer == "small" ? 40 : 75;
                const containerWidth = dojo.getComputedStyle(container).width.replace("px", "") - 130;        //buffer for character card

                if (addedItems == null || addedItems == undefined) { addedItems = []; }

                var totalWidth = 0;
                var modifiedTop = 0;
                var tmpUiPosition = 0;

                var newHeight;
                var cTopBefore;
                for (var i = 0; i < items.length; i++) {
                    const item = items[i];
                    console.log(item);
                    var uiItemWidth = this.uiItems.itemConfig[item.uiType].width + 8;
                    const spaceForOneLine = containerWidth - uiItemWidth;
                    const uiItemHeight = this.uiItems.itemConfig[item.uiType].height ? this.uiItems.itemConfig[item.uiType].height : 0;
                    var modifiedLeft = totalWidth;

                    cTopBefore = containerTop;
                    containerTop = Math.max(containerTop, item.htmlNode.getBoundingClientRect().height + 4, uiItemHeight, 50);
                    // this is to generate a new line for stuff
                    const noSpace = totalWidth > spaceForOneLine;
                    const extraItems = (item.uiType == "city_card" || item.uiType == "1x_gift") && modifiedTop === 0;
                    if (noSpace|| extraItems) {
                        console.log("NEW LINE: mdfTop "+modifiedTop);
                        console.log("NEW LINE: ctTop: "+containerTop);
                        console.log("NEW LINE: howCtopwasCalc: "+cTopBefore+","+item.htmlNode.getBoundingClientRect().height + 4+","+uiItemHeight);
                        modifiedTop += containerTop;
                        console.log("NEW LINE: NEW mdfTop: "+modifiedTop);
                        modifiedLeft = 0;
                        totalWidth = 0;
                        // bug here
                        newHeight = modifiedTop + containerTop;
                        dojo.setStyle(container, "height", newHeight + "px");
                        console.log("NEW LINE: NEW HEIGHT: " + newHeight + "px");
                        containerTop = item.htmlNode.getBoundingClientRect().height + 4;
                        console.log("NEW LINE: NEW ctTop: "+ containerTop);
                    }
                    else {
                        console.log("ctTop: "+containerTop);
                        console.log("howCtopwasCalc: "+cTopBefore+" , "+item.htmlNode.getBoundingClientRect().height + 4+" , "+uiItemHeight);
                    }
                    var anim = this.slideToObjectPos(item.htmlNode, container, modifiedLeft, modifiedTop);
                    if (addedItems.find(function (a) { return a.uid == item.uid })) {
                        anim.onEnd = dojo.partial(this.placeOnNewParent, modifiedTop, modifiedLeft, container);
                    }
                    anims.push(anim);

                    if (i > 0 && item.uiType != items[i - 1].uiType) { tmpUiPosition = 0; }
                    item.uiPosition = tmpUiPosition;
                    totalWidth += uiItemWidth;
                    tmpUiPosition++;
                }

                const animChain = dojo.fx.combine(anims);
                newHeight = modifiedTop + containerTop;
                animChain.onEnd = function () {
                    if (onAnimateEndCallback) { onAnimateEndCallback(); }
                    this.runningPlayerMatAnimations[playerId + itemContainer] = null;
                    dojo.setStyle(container, "height", newHeight + "px");
                }.bind(this);
                console.log("NEW HEIGHT: "+ newHeight + "px");
                console.log("NEW HEIGHT calc: mtop"+ modifiedTop + ", ctop:" + containerTop);

                if (this.runningPlayerMatAnimations[playerId + itemContainer]) {
                    this.runningPlayerMatAnimations[playerId + itemContainer].stop(true);       //stop any running animation
                }
                this.runningPlayerMatAnimations[playerId + itemContainer] = animChain;
                animChain.play();
            },

            repositionPlayerMat: function (itemContainer, addedItems, playerId)      //updates player mat with new/removed items
            {
                this.repositionPlayerMatWithCallback(itemContainer, addedItems, null, playerId);
            },

            setUiPositionForDiceOnBoard: function (location, locationArg, locationHeight, playerId) {
                var dice = this.uiItems.getByUiType("die").filter(function (d) {
                    return d.data.location == location && d.data.location_arg == locationArg &&
                        d.data.location_height == locationHeight && d.data.player_id == playerId
                });

                dice.sort(this.sortUiItemsByWeightPosition.bind(this.uiItems));
                for (var i = 0; i < dice.length; i++) {
                    dice[i].uiPosition = i;
                }
            },

            setUiPositionForFiguresOnBoard: function (location, locationArg) {
                var figures = this.uiItems.getByUiType("figure").filter(function (f) { return f.data.location == location && f.data.location_arg == locationArg });
                for (var i = 0; i < figures.length; i++) {
                    figures[i].uiPosition = i;
                }
            },

            getNextAvailableUiPosition: function (uiType, location, locationArg) {
                var nextPosition = 0;
                var items = this.uiItems.getByUiType(uiType).filter(function (f) { return f.data.location == location && f.data.location_arg == locationArg });
                for (var i = 0; i < items.length; i++) {
                    if (items.find(function (item) { return item.uiPosition == i })) {
                        nextPosition = i + 1;
                    }
                }
                return nextPosition;
            },

            repositionPlayerMats: function () {
                for (var playerId in this.gamedatas.players) {
                    for (var containerType in this.playerMatContainerUiTypes) {
                        this.repositionPlayerMat(containerType, null, playerId);
                    }
                }
            },

            animateDiscardContract: function (contractId, delay, playerId) {
                var discardedContract = this.uiItems.getByUiTypeAndId("contract", contractId);
                discardedContract.data.id = -1;
                discardedContract.data.location = "box";
                this.fadeOutAndDestroy(discardedContract.htmlNode, 500, delay);      //give time for resources

                if (playerId > 0 && delay > 0) {
                    setTimeout(function () {
                        this.repositionPlayerMat("large", null, playerId);
                    }.bind(this), delay);
                }
                else if (playerId > 0) {
                    this.repositionPlayerMat("large", null, playerId);
                }
            },

            animateDiscardCityCard: function (cityCardId, delay, playerId) {
                var discardedCityCard = this.uiItems.getByUiTypeAndTypeArg("city_card", cityCardId);
                discardedCityCard.data.id = -1;
                discardedCityCard.data.location = "box";
                this.fadeOutAndDestroy(discardedCityCard.htmlNode, 500, delay);      //give time for resources

                if (playerId > 0 && delay > 0) {
                    setTimeout(function () {
                        this.repositionPlayerMat("large", null, playerId);
                    }.bind(this), delay);
                }
                else if (playerId > 0) {
                    this.repositionPlayerMat("large", null, playerId);
                }
            },


            moveUiItemToParentContainer: function (uiItem, parentContainer) {
                if (parentContainer != null) {
                    dojo.place(uiItem.htmlNode, parentContainer);
                    this.positionUiItem(uiItem);
                    this.changeUiItemPointerEvents(uiItem);
                    this.changeUiItemZIndex(uiItem);
                }
            },

            getParentContainerForUiItem: function (uiItem) {
                var containerName = "board";
                var isPlayerItem = this.uiItems.isPlayerItem(uiItem);
                if (isPlayerItem && this.getContainerTypeByUiType(uiItem.uiType)) {
                    containerName = this.getContainerTypeByUiType(uiItem.uiType) + "-container-" + this.uiItems.getPlayerId(uiItem);
                }
                else if (uiItem.uiType == "character" || uiItem.uiType == "character_spot") {
                    containerName = "playerCharacter-" + uiItem.data.player_id;
                }
                else if (uiItem.uiType == "die" && ["gunj"].includes(uiItem.data.location))     //handle dice that are outside of "player mat" but not on board but in player container.
                {
                    containerName = "playerCharacter-" + uiItem.data.player_id;
                }
                else if (isPlayerItem || (uiItem.uiType == "city_card" && uiItem.data.location == "pick_character")) {
                    containerName = null;
                }
                return containerName;
            },

            drawUiItem: function (uiItem) {
                var parentContainer = this.getParentContainerForUiItem(uiItem);

                if (uiItem.uiType == "die" && parentContainer == "board") {
                    this.setUiPositionForDiceOnBoard(uiItem.data.location, uiItem.data.location_arg, uiItem.data.location_height, uiItem.data.player_id);
                }
                else if (uiItem.uiType == "figure") {
                    this.setUiPositionForFiguresOnBoard(uiItem.data.location, uiItem.data.location_arg);
                }
                else if (uiItem.uiType == "trading_post" && parentContainer == "board") {
                    uiItem.uiPosition = uiItem.data.location_position;
                }

                this.moveUiItemToParentContainer(uiItem, parentContainer);
                this.uiItems.addTooltip(uiItem);
            },

            destroyGoalAnchors: function () {
                dojo.forEach(dojo.query(".goal_post_anchor"), dojo.destroy);
            },

            showAllGoalAnchors: function () {
                var playerId = this.player_id;
                var allGoalCards = this.uiItems.getByUiType("goal_card").filter(function (g) { return g.data.location == "goal_hand" && g.data.location_arg == playerId && g.data.type_arg != 29; });
                if (allGoalCards.length == 2)       //only after selected
                {
                    for (var i = 0; i < allGoalCards.length; i++) {
                        allGoalCards[i].isGoalShowing = true;
                    }
                    this.showGoalAnchors(allGoalCards, null);
                    this.adjustGoalCardStyle(allGoalCards);
                }
            },

            showGoalAnchors: function (goalCards, triggeringUiItem) {
                this.destroyGoalAnchors();
                for (var i = 0; i < goalCards.length; i++) {
                    var goalCardData = this.gamedatas.material.goal_card_types[goalCards[i].data.type_arg];
                    for (var j = 0; j < goalCardData.cities.length; j++) {
                        var cityId = goalCardData.cities[j];
                        var anchor = dojo.create("div", { "class": "piece goal_post_anchor" });
                        var coords = this.getMapNodeMarginBox(cityId);
                        var widthBuffer = this.gamedatas.material.board[cityId].type == "small_city" ? 54 : 80;
                        var topBuffer = -12;
                        dojo.setStyle(anchor, "top", coords.t + topBuffer + "px");
                        dojo.setStyle(anchor, "left", coords.l + widthBuffer + "px");
                        dojo.place(anchor, "board");

                        if (triggeringUiItem && triggeringUiItem == goalCards[i]) {
                            dojo.addClass(anchor, "goal_anchor_animate");
                        }
                    }
                }
            },

            adjustGoalCardStyle: function (allGoalCards) {
                for (var i = 0; i < allGoalCards.length; i++) {
                    if (allGoalCards[i].isGoalShowing) {
                        dojo.setStyle(allGoalCards[i].htmlNode, "box-shadow", "black 4px 4px 10px 2px");
                    }
                    else {
                        dojo.setStyle(allGoalCards[i].htmlNode, "box-shadow", "");
                    }
                }
            },

            drawUi: function () {
                for (var i = 0; i < this.uiItems.length; i++) {
                    var uiItem = this.uiItems[i];
                    this.drawUiItem(uiItem);
                }
                this.repositionPlayerMats();
                if (this.prefs[100].value == 0) { this.showAllGoalAnchors(); }
            },

            setupCharacterUiItems: function (characterType, playerId) {
                var characterSpots = this.uiItems.getByUiType("character_spot").filter(function (c) { return c.data.character_type == characterType });
                for (var i = 0; i < characterSpots.length; i++) {
                    characterSpots[i].data.player_id = playerId;
                    this.drawUiItem(characterSpots[i]);
                }
            },

            showGoalSelection: function () {
                if (this.isCurrentPlayerActive()) {
                    var goalCards = this.uiItems.getByUiType("goal_card").filter(function (g) { return g.data.type_arg != 29; });        //29 = fake goal card (back only)
                    for (var i = 0; i < goalCards.length; i++) {
                        var goalCard = goalCards[i];
                        dojo.place(goalCard.htmlNode, "goalSelection");
                        this.uiItems.setBackgroundUiItem(goalCard);
                        dojo.setStyle(goalCard.htmlNode, "top", "");
                        dojo.setStyle(goalCard.htmlNode, "left", "");
                        this.removeTooltip(dojo.getAttr(goalCard.htmlNode, "id"));
                    }
                    dojo.setStyle("goalSelection", "display", "block");
                    this.uiItems.makeSelectable(goalCards);
                }
            },
            ///////////////////////////////////////////////////
            //// Player's action

            /*

                Here, you are defining methods to handle player's action (ex: results of mouse click on
                game objects).

                Most of the time, these methods:
                _ check the action is possible at this game state.
                _ make a call to the game server

            */
            onClickUiItem: function (evt) {
                if (evt != null) {
                    var uid = dojo.getAttr(evt.currentTarget, "data-uid").replace("uid-", "");
                    var uiItem = this.uiItems.getByUid(uid);

                    if (uiItem.isSelectable && this[this.currentMove] != undefined) {
                        this.uiItems.toggleSelection(uiItem);
                        this[this.currentMove](uiItem);
                    }
                    else if (uiItem.uiType == "figure") {
                        var name = this.gamedatas.material.board[uiItem.data.location_arg].name;
                        if (name == "Oasis") { name = _("an oasis") };
                        this.showMessage(dojo.string.substitute(_("This figure is in ${location_name}"), { location_name: name }), "info");
                    }
                    else if (uiItem.uiType == "goal_card") {
                        this.onClickToggleGoalAnchors(uiItem);
                    }
                }
            },

            onClickPlayerAid: function () {
                this.summaryDialog = new ebg.popindialog();
                this.summaryDialog.create('gameSummaryDialog');
                this.summaryDialog.setTitle(_("Player Aid"));
                this.summaryDialog.setMaxWidth(640);
                var html = this.format_block("jstpl_player_aid", {
                    "player_aid_bonus_actions_title": _("Bonus Actions – Always freely chosen."),
                    "player_aid_bonus_contract": _("1.	 Complete 1 contract"),
                    "player_aid_bonus_coin": _("2.	 Take 3 coins"),
                    "player_aid_bonus_reroll": _("3.	 Reroll 1 die"),
                    "player_aid_bonus_adjustdie": _("4.	 Adjust 1 die result by 1"),
                    "player_aid_bonus_blackdie": _("5.	 Take 1 black die (once per turn)"),
                    "player_aid_bonus_contract_desc": _("Return all necessary goods to the <b>supply</b>, receive the <b>rewards</b> for the contract, and add the completed contract to your drawer."),
                    "player_aid_bonus_coin_desc": _("Place <b>1 die</b> onto the money bag and take 3 coins regardless of die value. You have nothing to pay if other dice are present here."),
                    "player_aid_bonus_reroll_desc": _("Return <b>1 camel</b> to the supply and reroll <b>1 die</b>."),
                    "player_aid_bonus_adjustdie_desc": _("Return <b>2 camels</b> to the supply and adjust <b>1 die</b> value <b>up or down by 1</b>."),
                    "player_aid_bonus_blackdie_desc": _("Return <b>3 camels</b> to the supply and take <b>1 black die</b>, roll it, and add it to your player board. <b>Note:</b> You can only take <b>1 black die per turn</b>."),
                });
                this.summaryDialog.setContent(html);
                this.summaryDialog.show();
            },

            onClickTransparentFigure: function (evt) {
                let curOpacity = dojo.getStyle($("transparent_figure"), "opacity");
                let newOpacity = curOpacity == 1 ? 0.5 : 1;
                let figures = this.uiItems.getByUiType("figure");
                dojo.forEach(figures, function (f) {
                    dojo.setStyle(f.htmlNode, "opacity", newOpacity);
                });
                dojo.setStyle($("transparent_figure"), "opacity", newOpacity);
            },

            onClickConfirmCharacter: function (evt) {
                this.checkAction("pickCharacter");
                var uiItem = this.uiItems.getFirstSelectedItemByUiType("character");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/pickCharacter.html", { lock: true, "character_type": uiItem.data.character_type }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            onClickConfirmGoals: function (evt) {
                this.checkAction("pickGoalCards");
                var goalCards = this.uiItems.getSelectedItems();
                if (goalCards.length != 2) {
                    this.showMessage(_("Must pick exactly two goal cards"), "error");
                }
                else {
                    var card_ids = goalCards.map(function (g) { return g.data.id; }).join("_");
                    this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/pickGoalCards.html", { lock: true, "card_ids": card_ids }, this,
                        function (result) { this.animateGoalCards(goalCards); },
                        function (error) { }
                    );
                    this.showGoalAnchors([]);
                }
            },

            onClickToggleGoalAnchors: function (uiItem) {
                var playerId = this.player_id;
                if (uiItem.data.type_arg == 29)     //fake goal card, nothing to do
                    return;

                if (this.prefs[100].value != 0) {
                    uiItem.isGoalShowing = uiItem.isGoalShowing == undefined ? true : !uiItem.isGoalShowing;
                }
                var allGoalCards = this.uiItems.getByUiType("goal_card").filter(function (g) { return g.data.location == "goal_hand" && g.data.location_arg == playerId && g.data.type_arg != 29; });
                var selectedGoalCards = allGoalCards.filter(function (g) { return g.isGoalShowing; });
                this.showGoalAnchors(selectedGoalCards, uiItem);
                this.adjustGoalCardStyle(allGoalCards);
            },

            onChangeGoalAnchorPreference: function (evt) {
                var playerId = this.player_id;
                var value = evt.currentTarget.value;
                this.prefs[100].value = value;
                var allGoalCards = this.uiItems.getByUiType("goal_card").filter(function (g) { return g.data.location == "goal_hand" && g.data.location_arg == playerId && g.data.type_arg != 29; });
                if (allGoalCards.length == 2)       //preference change only matters after goal selection
                {
                    if (value == 0) {
                        this.showAllGoalAnchors();
                    }
                    else {
                        for (var i = 0; i < allGoalCards.length; i++) {
                            allGoalCards[i].isGoalShowing = false;
                        }
                        this.adjustGoalCardStyle(allGoalCards);
                        this.destroyGoalAnchors();
                    }
                }
            },

            onClickUsePlayerPiece: function (pieceId) {
                this.checkAction("usePlayerPiece");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/usePlayerPiece.html", { lock: true, "piece_id": pieceId }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            onClickChooseResource: function (resourceType) {
                this.checkAction("chooseResource");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/chooseResource.html", { lock: true, "choice": resourceType }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            onClickRerollDie: function (evt) {
                this.checkAction("rerollDie");
                var die = this.uiItems.getFirstSelectedItemByUiType("die");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/rerollDie.html", { lock: true, "die_id": die.data.id }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            onClickBumpDie: function (direction) {
                this.checkAction("bumpDie");
                var die = this.uiItems.getFirstSelectedItemByUiType("die");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/bumpDie.html", { lock: true, "die_id": die.data.id, "up_or_down": direction }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            switchToClientChangeDie: function (gift_id) {
                this.setClientState("client_changeDie", { descriptionmyturn: _("Change dice to value"), args: { gift_id: gift_id } });
            },

            switchToClientGiftPickTradingPost: function (giftId, boardIds) {
                this.setClientState("client_gift10PickBoardId", { descriptionmyturn: _("Select trading post"), args: { giftId: giftId, boardIds: boardIds } });
            },

            onClickChangeDie: function (evt) {
                this.switchToClientChangeDie(null);
            },

            onClickChangeDieValue: function (value) {
                this.checkAction("changeDice");
                var selectedDice = this.uiItems.getSelectedItemsByUiType("die");
                var dice_ids = selectedDice.map(function (d) { return d.data.id; });
                var gift_id = this.currentMoveArgs.gift_id;
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/changeDice.html", { lock: true, "dice_ids": dice_ids.join("_"), "new_value": value, "gift_id": gift_id }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            onClickBuyBlackDie: function (evt) {
                if (this.playerResources[this.player_id]["camel"] < 3) {
                    this.showMessage("Need 3 camels to buy black die", "error");
                }
                else {
                    this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/buyBlackDie.html", { lock: true }, this,
                        function (result) { },
                        function (error) { }
                    );
                }
            },

            onClickPickCompensation: function (camel, coin) {
                this.checkAction("pickCompensation");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/pickCompensation.html", { lock: true, "camel": camel, "coin": coin }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            onClickCancel: function (evt) {
                this.restoreServerGameState();
            },

            onClickUndo: function (evt) {
                this.checkAction("undo");
                this.confirmationDialog(_('Are you sure you want to undo your move?'), dojo.hitch(this, function () {
                    this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/undo.html", { lock: true }, this,
                        function (result) { },
                        function (is_error) { }
                    );
                }));
            },

            onClickPass: function (evt) {
                this.checkAction("pass");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/pass.html", { lock: true }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            onClickSkipTravel: function (evt) {
                this.checkAction("skipTravel");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/skipTravel.html", { lock: true }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            onClickSkipPickContract: function (evt) {
                this.checkAction("skipContract");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/skipContract.html", { lock: true }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            onClickSkipCityAward: function (evt) {
                this.checkAction("skipChooseCityAward");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/skipChooseCityAward.html", { lock: true }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            onClickSkipTriggerOtherCityBonus: function (evt) {
                this.checkAction("skipTriggerOtherCityBonus");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/skipTriggerOtherCityBonus.html", { lock: true }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            onClickConfirmMoveTradingPost: function (evt) {
                this.checkAction("moveTradingPost");
                var tradingPost = this.uiItems.getFirstSelectedItemByUiType("trading_post");
                if (tradingPost != null) {
                    this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/moveTradingPost.html", { lock: true, selectedTradingPostId: tradingPost.data.id }, this,
                        function (result) {
                            this.resetUiItemsZIndex(this.uiItems.getPlayerUiItems("trading_post", "board", this.player_id));
                            this.uiItems.resetAllMapNodeBorders();
                        },
                        function (error) { }
                    );
                }
                else {
                    this.showMessage(_("You must select a trading post to move"), "error");
                }
            },

            onClickSkipMoveTradingPost: function (evt) {
                this.checkAction("skipMoveTradingPost");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/skipMoveTradingPost.html", { lock: true }, this,
                    function (result) {
                        this.resetUiItemsZIndex(this.uiItems.getPlayerUiItems("trading_post", "board", this.player_id));
                        this.uiItems.resetAllMapNodeBorders();
                    },
                    function (error) { }
                );
            },

            onClickActivateMultipleCityCard: function (numTimes, paymentDetails, evt) {
                this.checkAction("activateMultipleCityCard");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/activateMultipleCityCard.html", { lock: true, "num_times": numTimes, "payment_details": paymentDetails }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            onClickActivateChoiceCityCard: function (cardType, choiceIndex, maxValue, evt) {
                var cardData = this.gamedatas.material.city_cards[cardType];
                var numRemaining = 99;
                var costs = cardData.choice[choiceIndex].cost;
                for (var costType in costs) {
                    var amount = costs[costType];
                    numRemaining = this.playerResources[this.player_id][costType] / amount;
                }
                numRemaining = Math.min(numRemaining, maxValue);
                this.setClientState("client_playerChooseCityCardAward", {
                    descriptionmyturn: _("${you} must select number of times to activate choice"),
                    args: { card_type: cardType, choiceIndex: choiceIndex, num_remaining: numRemaining, isMultipleViaChoice: true }
                });
            },

            onClickActivateExchangeCityCard: function (exchangeType, evt) {
                this.checkAction("activateExchangeCityCard");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/activateExchangeCityCard.html", { lock: true, "exchange_type": exchangeType }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            sendTriggerBonus: function (bonusId) {
                this.checkAction("triggerBonus");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/triggerBonus.html", { lock: true, "bonus_id": bonusId }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            sendFulfillGift: function (selectedGift, boardId) {
                this.checkAction("fulfillGift");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/fulfillGift.html", { lock: true, "gift_id": selectedGift.data.id, "board_id": boardId }, this,
                    function (result) { },
                    function (error) { }
                );
            },

            sendFulfillContract: function (selectedContract) {
                this.checkAction("fulfillContract");
                if (this.hasResources(this.gamedatas.material["contracts"][selectedContract.data.type]["cost"], this.player_id)) {
                    if (this.playerResources[this.player_id])
                        this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/fulfillContract.html", { lock: true, "contract_id": selectedContract.data.id },
                            function (result) { },
                            function (error) { }
                        );
                }
                else {
                    this.uiItems.toggleSelection(selectedContract);
                    this.showMessage(_("You don't have the resources to fulfill this contract"), "error");
                    this[this.currentMove]();
                    this.updatePlayerTurnButtons([]);
                }
            },

            sendFulfillArghun: function (selectedCityCard) {
                this.checkAction("fulfillArghun");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/fulfillArghun.html", { lock: true, "citycard_id": selectedCityCard.data.id },
                    function (result) { },
                    function (error) { }
                );
            },

            sendPlaceDie: function (selectedDice, selectedPlace, selectedAward, usedAgentsGifts) {
                this.checkAction("placeDie");
                var die_ids = selectedDice.map(function (d) { return d.data.id; });
                var place = selectedPlace.uiType == "city_card" ? "city_card" : selectedPlace.data.place;
                var index = selectedPlace.uiType == "city_card" ? selectedPlace.data.type_arg : selectedPlace.data.index;
                var selectedAwardIndex = selectedAward ? selectedAward.data.index : 0;
                var giftFreeDiePlacementId = usedAgentsGifts.find(function (g) { return g.uiType == "gift" && g.data.type_arg == 7 });
                giftFreeDiePlacementId = giftFreeDiePlacementId ? giftFreeDiePlacementId.data.id : null;
                var numCoins = 0;

                if ((this.myCharacterType != 6 && giftFreeDiePlacementId == null) && this.getDiceOnUiItem(selectedPlace).length > 0) {
                    numCoins = Math.min.apply(Math, selectedDice.map(function (d) { return parseInt(d.data.value) }));
                }

                if (this.hasResources({ "coin": numCoins }, this.player_id) || place == "coin3") {
                    this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/placedie.html", {
                        lock: true, "place": place, "index": index, "award_index": selectedAwardIndex,
                        "die_ids": die_ids.join("_"), "gift_free_placement_id": giftFreeDiePlacementId
                    }, this,
                        function (result) { this.uiItems.resetAllSelectable() },
                        function (error) { this.onClickCancel(); }
                    );
                }
                else {
                    this.showMessage(_("Insufficent coins to place die here"), "error");
                    this.onClickCancel();
                }
            },

            sendTravel: function (selectedFigure, selectedDstNode) {
                this.checkAction("travel");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/travel.html", { lock: true, "figure_id": selectedFigure.data.id, "dst_id": selectedDstNode.data.id }, this,
                    function (result) { },
                    function (error) { this.restoreServerGameState(); }
                );
            },

            sendPickContract: function (selectedContract, replacedContract) {
                this.checkAction("pickContract");
                var replacedContractId = replacedContract != null ? replacedContract.data.id : null;
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/pickcontract.html", {
                    lock: true, "contract_id": selectedContract.data.id,
                    "replaced_contract_id": replacedContractId
                },
                    function (result) { },
                    function (error) { }
                );
            },

            sendTriggerOtherCityBonus: function (cityBonus) {
                this.checkAction("triggerOtherCityBonus");
                this.ajaxcall("/marcopoloexpansions/marcopoloexpansions/triggerothercitybonus.html", { lock: true, "city_bonus_type_arg": cityBonus.data.type_arg },
                    function (result) { },
                    function (error) { }
                );
            },
            ///////////////////////////////////////////////////
            //// Reaction to cometD notifications

            /*
                setupNotifications:

                In this method, you associate each of your game notifications with your local method to handle it.

                Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                      your marcopoloexpansions.game.php file.

            */
            setupNotifications: function () {
                console.log('notifications subscriptions setup');

                dojo.subscribe('pickCharacter', this, "notif_pickCharacter");
                dojo.subscribe('characterUpdate', this, "notif_characterUpdate");
                dojo.subscribe('goalCard', this, "notif_goalCard");
                dojo.subscribe('revealGoalCard', this, "notif_revealGoalCard");
                dojo.subscribe('updateDice', this, "notif_updateDice")
                dojo.subscribe('resourceChange', this, "notif_resourceChange");
                dojo.subscribe('travel', this, "notif_travel");
                dojo.subscribe('boxPiece', this, "notif_boxPiece")
                dojo.subscribe('placeTradingPost', this, "notif_placeTradingPost");
                dojo.subscribe('contract', this, "notif_contract");
                dojo.subscribe('gift', this, "notif_gift");
                dojo.subscribe('slideContracts', this, "notif_slideContracts");
                dojo.subscribe('fulfillContract', this, "notif_fulfillContract");
                dojo.subscribe('fulfillArghun', this, "notif_fulfillArghun");
                dojo.subscribe('moveHourGlass', this, "notif_moveHourGlass");

                this.notifqueue.setSynchronous('gift', 500);
                this.notifqueue.setSynchronous('updateDie', 500);
                this.notifqueue.setSynchronous('updateDice', 500);
                this.notifqueue.setSynchronous('resourceChange', 500);
                this.notifqueue.setSynchronous('pickCharacter', 1000);
                this.notifqueue.setSynchronous('characterUpdate', 1000);
                this.notifqueue.setSynchronous('pickedGoalCards', 1000);
            },

            notif_pickCharacter: function (notif) {
                var playerId = notif.args.player_id;
                var characterType = notif.args.character_type;
                var uiItem = this.uiItems.getByUiType("character").find(function (c) { return c.data.character_type == characterType; });
                uiItem.data.player_id = playerId;
                if (this.player_id == playerId) { this.myCharacterType = characterType }
                this.setupCharacterUiItems(characterType, playerId);

                dojo.setStyle("characterSelectionDescription", "display", "none");
                dojo.setStyle(uiItem.htmlNode, "zIndex", 500);
                var anim = this.slideToObject(uiItem.htmlNode, "playerCharacter-" + uiItem.data.player_id, 1000);
                anim.onEnd = function (fx) {
                    dojo.setStyle(fx, "top", "");
                    dojo.setStyle(fx, "left", "");
                    this.changeUiItemZIndex(uiItem);
                    dojo.place(fx, "playerCharacter-" + playerId);
                    this.uiItems.addTooltip(uiItem);
                }.bind(this);
                anim.play();
            },

            notif_characterUpdate: function (notif) {
                for (var i = 0; i < notif.args.data.length; i++) {
                    var uiItem = this.uiItems.createAndAddItem(notif.args.new_type, notif.args.data[i]);
                    this.drawUiItem(uiItem);
                }
                if (notif.args.new_type == "die" || notif.args.new_type == "city_card") {
                    this.repositionPlayerMats();
                }
                else if (notif.args.new_type == "trading_post") {
                    this.updateResourceInfo(notif.args.player_id);
                }
            },

            notif_goalCard: function (notif) {
                for (var cardId in notif.args.cards) {
                    var card = notif.args.cards[cardId];
                    card.location = "goalSelection";
                    this.uiItems.createAndAddItem("goal_card", card);
                }
            },

            notif_revealGoalCard: function (notif) {
                var goalCardsUiItems = [];
                for (var cardId in notif.args.cards) {
                    var card = notif.args.cards[cardId];
                    if (this.uiItems.getByUiTypeAndId("goal_card", cardId) == null) {
                        var item = this.uiItems.createAndAddItem("goal_card", card);
                        this.drawUiItem(item);
                        goalCardsUiItems.push(item);
                    }
                }
                if (goalCardsUiItems.length > 0) {
                    var goalBacks = this.uiItems.getByUiType("goal_card").filter(function (g) { return g.data.location_arg == goalCardsUiItems[0].data.location_arg && g.data.type_arg == 29 });
                    for (var i = 0; i < goalBacks.length; i++) {
                        goalBacks[i].data.location = "box";
                        dojo.destroy(goalBacks[i].htmlNode);
                    }
                    this.repositionPlayerMat("large", goalCardsUiItems, goalCardsUiItems[0].data.location_arg);
                }
            },

            notif_moveHourGlass: function (notif) {
                this.updateHourGlass(notif.args.player_id);
            },

            notif_updateDice: function (notif) {
                var dice = notif.args.dice;
                var diceArray = [];
                var uiItemsToAnimate = [];
                for (var dieId in dice) {
                    var dieInfo = dice[dieId];
                    var die = this.uiItems.getByUiTypeAndId("die", dieInfo.die_id);
                    if (dieInfo.die_value != undefined && notif.args.shake == false) {
                        die.data.value = dieInfo.die_value;
                    }
                    else if (dieInfo.die_value != undefined && notif.args.shake == true) {
                        die.data.after_shake_value = dieInfo.die_value;
                    }
                    die.data.location = dieInfo.die_location;
                    die.data.location_arg = dieInfo.die_location_arg;
                    die.data.location_height = dieInfo.die_location_height;
                    die.data.player_id = dieInfo.die_player_id;
                    if (!notif.args.shake) {
                        this.setUiPositionForDiceOnBoard(die.data.location, die.data.location_arg, die.data.location_height, die.data.player_id);
                        this.uiItems.setBackgroundUiItem(die);
                    }
                    diceArray.push(die);
                    uiItemsToAnimate.push(die);
                }

                if (notif.args.shake) {
                    var shakeDiceClosure = this.createShakeDiceClosure(diceArray);
                    this.animateAndMoveUiItemsWithCallback(uiItemsToAnimate, shakeDiceClosure);
                }
                else {
                    this.animateAndMoveUiItems(uiItemsToAnimate);
                }
            },

            notif_resourceChange: function (notif) {
                var playerId = notif.args.player_id;
                if (notif.args.location.startsWith("city_card")) {
                    var cityCardType = notif.args.location.replace("city_card_", "");
                    notif.args.location = "uid-" + this.uiItems.getByUiType("city_card").find(c => c.data.type_arg == cityCardType && c.data.location != "pick_character").uid;
                }
                else if (notif.args.location.startsWith("character")) {
                    notif.args.location = "playerCharacter-" + playerId;
                }
                else if (notif.args.location.startsWith("contract")) {
                    var contractId = notif.args.location.replace("contract_", "");
                    notif.args.location = "uid-" + this.uiItems.getByUiTypeAndId("contract", contractId).uid;
                }
                else if (notif.args.location == "gunj_0") {
                    notif.args.location = "uid-" + this.uiItems.getByUiType("character_spot").find(function (s) { return s.data.place == "gunj" && s.data.index == 0; }).uid;
                }
                else if (notif.args.location.startsWith("gift")) {
                    var giftId = notif.args.location.replace("gift_", "");
                    notif.args.location = "uid-" + this.uiItems.getByUiTypeAndId("gift", giftId).uid;
                }
                var runningDelay = 0;
                for (var resourceType in notif.args.resource_changes) {
                    var amount = parseInt(notif.args.resource_changes[resourceType]);
                    for (var i = 0; i < amount; i++) {
                        var tempHtml = '<span class="piece panel ' + resourceType + '"><span>';
                        var destId = "panel_" + resourceType + "_" + notif.args.player_id;
                        var sourceId = notif.args.location;
                        var parentId = "board";         //todo handle second board for expansion
                        if (notif.args.negate) {
                            sourceId = destId;
                            destId = notif.args.location;
                            parentId = "panel_" + notif.args.player_id;
                        }
                        this.slideTemporaryObject(tempHtml, parentId, sourceId, destId, 500, runningDelay + 100 * i);
                    }
                    runningDelay += amount * 100 + 100;
                    amount = notif.args.negate ? -1 * amount : amount;
                }
                for (var resourceType in notif.args.absolute_resources) {
                    var newAmount = notif.args.absolute_resources[resourceType];
                    this.playerResources[playerId][resourceType] = newAmount;
                }
                this.updateResourceInfo(playerId);
            },

            notif_travel: function (notif) {
                var figure = this.uiItems.getByUiType("figure").find(function (f) { return f.data.id == notif.args.figure_id });
                figure.uiPosition = this.getNextAvailableUiPosition("figure", figure.data.location, notif.args.dst_id);
                figure.data.location_arg = notif.args.dst_id;
                this.animateAndMoveUiItems([figure]);
            },

            notif_placeTradingPost: function (notif) {
                var tradingPost = this.uiItems.getByUiTypeAndId("trading_post", notif.args.trading_post_id);
                tradingPost.uiPosition = notif.args.location_position;
                tradingPost.data.location = notif.args.location;
                tradingPost.data.location_arg = notif.args.location_arg;
                dojo.place(tradingPost.htmlNode, $("playerMat-" + tradingPost.data.player_id));
                var tradingPostPanel = dojo.marginBox($("panel-trading_post-" + tradingPost.data.player_id));
                dojo.setStyle(tradingPost.htmlNode, "top", tradingPostPanel.t + "px");
                dojo.setStyle(tradingPost.htmlNode, "left", tradingPostPanel.l + "px");
                this.animateAndMoveUiItems([tradingPost]);
                this.updateResourceInfo(tradingPost.data.player_id);
            },

            notif_gift: function (notif) {
                var gifts = [];
                var giftsData = notif.args.gifts_data;
                for (var i = 0; i < giftsData.length; i++) {
                    var gift = this.uiItems.createAndAddItem("gift", giftsData[i]);
                    dojo.place(gift.htmlNode, "roundContainer");
                    this.placeOnObject(gift.htmlNode, "gift_pile");
                    gifts.push(gift);
                }
                this.animateAndMoveUiItems(gifts);
            },

            notif_contract: function (notif) {
                if (notif.args.discard_contract_id) {
                    this.animateDiscardContract(notif.args.discard_contract_id, 0, notif.args.player_id);
                }

                if (notif.args.contract_id) {
                    var contract = this.uiItems.getByUiTypeAndId("contract", notif.args.contract_id);
                    if (contract == null) {
                        contract = this.uiItems.createAndAddItem("contract", notif.args.contract_data);
                        dojo.place(contract.htmlNode, "roundContainer");
                        this.placeOnObject(contract.htmlNode, "contract_special_pile");
                        this.uiItems.addTooltip(contract);
                    }
                    contract.data.location = "hand";
                    contract.data.location_arg = notif.args.player_id;
                    contract.uiPosition = this.getNextAvailableUiPosition("contract", "hand", notif.args.player_id);

                    this.animateAndMoveUiItems([contract]);
                }
            },

            notif_fulfillContract: function (notif) {
                var playerId = notif.args.player_id;
                var delay = 0;
                this.animateDiscardContract(notif.args.contract_id, delay, playerId);
                this.gamedatas.player_contracts_completed[playerId] += 1;
                dojo.setAttr($('contracts-complete-' + playerId), "innerHTML", this.gamedatas.player_contracts_completed[playerId]);
            },

            notif_fulfillArghun: function (notif) {
                var playerId = notif.args.player_id;
                var delay = 0;
                this.animateDiscardCityCard(notif.args.city_card_id, delay, playerId);
            },

            notif_slideContracts: function (notif) {
                var contracts = notif.args.contracts;
                var contractUiItems = [];

                if (notif.args.trigger_by.startsWith("round_") && notif.args.trigger_by != "round_1")         //discard existing contracts if new round
                {
                    var existingContracts = this.uiItems.getByUiType("contract").filter(function (c) { return c.data.location == "board"; });
                    for (var i = 0; i < existingContracts.length; i++) {
                        this.animateDiscardContract(existingContracts[i].data.id, 0, 0);         //playerId = 0 = board
                    }
                }

                for (var contractId in contracts) {
                    var contract = this.uiItems.getByUiTypeAndId("contract", contractId);
                    if (contract == null) {
                        contract = this.uiItems.createAndAddItem("contract", contracts[contractId]);
                        dojo.place(contract.htmlNode, "roundContainer");
                        this.placeOnObject(contract.htmlNode, notif.args.trigger_by);
                        this.uiItems.addTooltip(contract);
                    }
                    contract.data.location_arg = contracts[contractId].location_arg;
                    contractUiItems.push(contract);
                }
                this.animateAndMoveUiItems(contractUiItems);

                if (notif.args.trigger_by.startsWith("round_")) {
                    var roundNumber = parseInt(notif.args.trigger_by.replace("round_", ""));
                    this.updateCurrentRound(roundNumber);
                }
            },

            notif_boxPiece: function (notif) {
                var boxedPiece = this.uiItems.getByUiTypeAndId(notif.args.piece_type, notif.args.piece_id);
                if (boxedPiece.uiType == "outpost" || boxedPiece.uiType == "gift") {
                    var divId = dojo.getAttr(boxedPiece.htmlNode, "id");
                    this.removeTooltip(divId);
                }
                if (boxedPiece.clickHandler) { dojo.disconnect(boxedPiece.clickHandler); }
                this.fadeOutAndDestroy(boxedPiece.htmlNode, 500, 0);
                if (boxedPiece.uiType == "gift")            //wipe out id
                {
                    boxedPiece.data.id = -1;
                    boxedPiece.data.location = "box";
                    this.repositionPlayerMat("small", null, notif.args.player_id);
                }
                else if (boxedPiece.uiType == "1x_gift") {
                    boxedPiece.data.location = "box";
                    this.repositionPlayerMat("large", null, notif.args.player_id);
                }
            },
        });
    });
