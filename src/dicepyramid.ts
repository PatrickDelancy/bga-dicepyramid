/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Dice Pyramid implementation : © Patrick Delancy <patrick.delancy@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * dicepyramid.ts
 *
 * In this file, you are describing the logic of your user interface, in Typescript language.
 *
 */

class dicepyramid implements Game {
  private gamedatas: dicepyramidGamedatas;
  private player_id: string;
  private players: { [playerId: number]: Player };
  private playerNumber: number;

  private discardStock: VoidStock<Card>;
  private pyramidStock: CardStock<Card>;
  private relicStock: CardStock<Card>;
  private cardManager: CardManager<Card>;
  private playingRelicStock: CardStock<Card>;

  public CARDINFO: Record<number, ICardInfo>;

  constructor() {
    console.log("dicepyramid constructor");

    // Here, you can init the global variables of your user interface
    // Example:
    // this.myGlobalValue = 0;

    this.cardManager = new CardManager(this, {
      getId: (card) => `card-${card.id}`,
      setupDiv: (card, div) => {
        if (card.flipped) div.classList.add(`flipped`);
      },
      setupFrontDiv: (card, div) => {
        let cardEl = div.parentElement.parentElement;
        if (card.type) cardEl.classList.add(`card-type-${card.type}`);
        cardEl.classList.toggle("used", card.used);

        if (card.type && card.type_arg) {
          div.style.backgroundPositionY = `${((card.type_arg - 1) * 100) / 1}%`;
          div.style.backgroundPositionX = `${((card.type - 1) * 100) / 15}%`;

          const tooltipHtml = jstpl_CardHelpDialog(card, this.CARDINFO[card.type]);
          if (tooltipHtml) {
            window.gameui.addTooltipHtml(div.id, tooltipHtml, 500);
          }
        }
      },
      setupBackDiv: (card, div) => {
        //div.classList.add("card-back");
      },
      isCardVisible: (card) => Boolean(card.type),
      cardWidth: 140,
      cardHeight: 202,
    });
  }

  public onGameUserPreferenceChanged(prefId: number, pref_value: number): any {
    switch (prefId) {
      case 120: // card icon overlays
        console.log((this as any).prefs);
        for (let [i, v] of Object.entries((this as any).prefs?.[prefId]?.values) as any) {
          console.log(i, v);
          v.cssPref && document.documentElement.classList.toggle(v.cssPref, pref_value == i);
        }
        break;
    }
  }

  /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
  public setup(gamedatas: dicepyramidGamedatas) {
    console.log("Starting game setup", gamedatas);

    this.CARDINFO = gamedatas.CARD_DATA;

    document.getElementById("game_play_area").insertAdjacentHTML(
      "afterbegin",
      `
      <span id="discard"></span>
      <div id="table">
        <div id="pyramid" class="pyramid">
          <div id="pyramid-stock" class="pyramid-stock"></div>
        </div>
        <div id="dice-box" class="dice-box">
          <div id="dice-box-header" class="dice-box-header"></div>
          <div id="dice" class="dice">
            <div data-slot-id="0" class="slot dice-slot"></div>
            <div data-slot-id="1" class="slot dice-slot"></div>
            <div data-slot-id="2" class="slot dice-slot"></div>
            <div data-slot-id="3" class="slot dice-slot"></div>
            <div data-slot-id="4" class="slot dice-slot"></div>
          </div>
          <div id="dice-box-commands" class="dice-box-commands"></div>
          <div id="playing-relic" class="playing-relic"></div>
        </div>
        <div id="relics" class="relics">
          <div id="relic-header" class="relic-header">${gamedatas.powerRelics ? _("Power Relics") : _("Relics")
      }<i class="fa6-solid fa6-magnifying-glass magnifying-glass"></i><i id="pin-relic-header" class="fa6-solid fa6-thumbtack pin"></i></div>
          <div id="relic-stock" class="relic-stock"></div>
        </div>
        <a href="#dice-box" id="scroll-to-dice" onclick="document.getElementById('dice-box').scrollIntoView({behavior:'smooth',block:'center'}); event?.preventDefault(); event?.stopPropagation();"><i class="fa6-solid fa6-dice"></i><i class="fa6-solid fa6-arrow-down"></i></a>
        <div id="pyramid-card-commands" class="card-commands"></div>
        <div id="relic-card-commands" class="card-commands"></div>
      </div>`
    );

    this.discardStock = new VoidStock(this.cardManager, document.getElementById("discard"));

    document.getElementById("relic-header").addEventListener("click", (e) => {
      let el = document.getElementById("relics");
      el.classList.toggle("expanded");
      if (!(el.classList.contains("expanded") || el.classList.contains("pinned"))) this.relicStock.unselectAll();
    });
    document.getElementById("pin-relic-header").addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      let el = document.getElementById("relics");
      el.classList.toggle("pinned");
      if (!(el.classList.contains("expanded") || el.classList.contains("pinned"))) this.relicStock.unselectAll();
    });
    document.getElementById("pyramid-card-commands").addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
    });
    document.getElementById("relic-card-commands").addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
    });

    this.pyramidStock = new SlotStock(this.cardManager, document.getElementById(`pyramid-stock`), {
      slotsIds: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
      mapCardToSlot: (card) => card.location_arg,
      slotClasses: ["pyramid-slot"],
      direction: "row",
    });
    if (gamedatas.pyramid) {
      this.pyramidStock.addCards(gamedatas.pyramid);
    }
    this.playingRelicStock = new LineStock(this.cardManager, document.getElementById("playing-relic"));

    this.relicStock = new LineStock(this.cardManager, document.getElementById(`relic-stock`), { direction: "row" });
    if (gamedatas.relics) {
      this.relicStock.addCards(gamedatas.relics);
    }
    if (gamedatas.dice) {
      this.setDiceValues(gamedatas.dice);
    }
    if (gamedatas.activeRelic) {
      this.playingRelicStock.addCard(gamedatas.activeRelic);
    }

    // Setup game notifications to handle (see "setupNotifications" method below)
    this.setupNotifications();

    console.log("Ending game setup");
  }

  ///////////////////////////////////////////////////
  //// Game & client states

  // onEnteringState: this method is called each time we are entering into a new game state.
  //                  You can use this method to perform some user interface changes at this moment.
  //
  public onEnteringState(stateName: string, e: any) {
    console.log("Entering state: " + stateName, e);

    // if ((this as any).isCurrentPlayerActive() == false || this.isReadOnly()) {
    //   console.log("Current player not active.");
    //   return;
    // }

    switch (stateName) {
      case "playerDiscardRelic":
      case "playerBeforeInitialRoll":
        document.querySelectorAll(".card.used").forEach((el) => el.classList.remove("used"));
        break;
    }
  }

  // onLeavingState: this method is called each time we are leaving a game state.
  //                 You can use this method to perform some user interface changes at this moment.
  //
  public onLeavingState(stateName: string) {
    console.log("Leaving state: " + stateName);

    switch (stateName) {
      case "playerTurn":
      case "playerPlayingRelic":
      default:
        this.disableDiceSelection();
        this.pyramidStock.setSelectionMode("none");
        this.relicStock.setSelectionMode("none");

        document
          .getElementById("table")
          .append(document.getElementById("pyramid-card-commands"), document.getElementById("relic-card-commands"));
        document.getElementById("dice-box-commands").innerHTML = "";
        document.getElementById("dice-box-header").innerHTML = "";
        document.getElementById("pyramid-card-commands").innerHTML = "";
        document.getElementById("relic-card-commands").innerHTML = "";
        break;
    }
  }

  // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
  //                        action status bar (ie: the HTML links in the status bar).
  //
  public onUpdateActionButtons(stateName: string, args: any) {
    console.log("onUpdateActionButtons: " + stateName, args, this.isReadOnly());

    const deselectDice = () => {
      this.clearSelectedDice(true);
      document.getElementById("btnRerollSelectedDice")?.classList.add("disabled");
    };

    const deselectPyramidCards = () => {
      this.pyramidStock.unselectAll(true);
      document.getElementById("table").appendChild(document.getElementById("pyramid-card-commands"));
    };

    const deselectRelicCards = () => {
      this.relicStock.unselectAll(true);
      document.getElementById("table").appendChild(document.getElementById("relic-card-commands"));
    };

    if ((this as any).isCurrentPlayerActive()) {
      switch (stateName) {
        case "playerBeforeInitialRoll": {
          this.disableDiceSelection();
          this.pyramidStock.setSelectionMode("single", []);
          this.relicStock.setSelectionMode("none");

          (this as any).statusBar.addActionButton(
            _("Roll Dice"),
            () => {
              let dice = this.getDice();
              dice.forEach((d) => this.getDiceElement(d.id)?.classList.add("rerolling"));
              setTimeout(() => {
                (this as any).bgaPerformAction("actPlayInitialRoll", {});
              }, 500);
            },
            {
              id: "btnPlayInitialRoll",
              color: "primary",
              classes: "attention delayed-start",
              destination: document.getElementById("dice-box-commands"),
            }
          );
          break;
        }
        case "playerTurn": {
          (this as any).statusBar.addActionButton(
            _("End Game (defeat)"),
            () => {
              (this as any).bgaPerformAction("actGiveUp", {});
            },
            {
              id: "btnGiveUp",
              color: "alert",
              classes: "disabled",
            }
          );

          this.enableDefeatButton(
            !args.rerolls && this.relicStock.getCards().length == 0 && !args.takeableCards?.length
          );

          // Dice
          if (args.rerolls) {
            document.getElementById("dice-box").dataset["action"] = "reroll";
            this.setDiceSelectable({}, (selectedDice) => {
              document.getElementById("btnRerollSelectedDice").classList.toggle("disabled", selectedDice.length === 0);
              deselectPyramidCards();
              deselectRelicCards();
            });
          } else {
            this.disableDiceSelection();
            if (this.gamedatas.gamestate["descriptionmyturnnorerolls"]) {
              debugger;
              (this as any).statusBar.setTitle(this.gamedatas.gamestate["descriptionmyturnnorerolls"], args);
            }
          }

          // Dice Button
          let diceBoxCmd = document.getElementById("dice-box-commands");
          (this as any).statusBar.addActionButton(
            dojo.string.substitute(_("Re-roll (${rerolls_remaining} roll(s) left)"), {
              rerolls_remaining: args.rerolls,
            }),
            () => {
              if (diceBoxCmd.classList.contains("submitting")) return;
              diceBoxCmd.classList.add("submitting");

              let selectedDice = this.getSelectedDice();
              selectedDice.forEach((d) => this.getDiceElement(d.id)?.classList.add("rerolling"));
              console.log("Rerolling ", selectedDice);
              setTimeout(() => {
                let idsString = selectedDice.map((d) => d.id).join(",");
                console.log("Rerolling ", selectedDice, idsString);
                (this as any)
                  .bgaPerformAction("actRerollDice", {
                    diceIds: idsString,
                  })
                  ?.then(() => diceBoxCmd.classList.remove("submitting"));
              }, 500);
            },
            {
              id: "btnRerollSelectedDice",
              color: "primary",
              classes: "disabled",
              destination: diceBoxCmd,
            }
          );

          // Pyramid
          this.pyramidStock.setSelectionMode("single", args.takeableCards);
          this.pyramidStock.onSelectionChange = (selection: Card[]) => {
            document.getElementById("btnTakeRoom").classList.toggle("disabled", !selection.length);

            if (selection.length > 0) {
              this.cardManager
                .getCardElement(selection[0])
                .appendChild(document.getElementById("pyramid-card-commands"));

              deselectDice();
              deselectRelicCards();
            } else {
              document.getElementById("table").appendChild(document.getElementById("pyramid-card-commands"));
            }
          };

          // Relics
          this.relicStock.setSelectionMode("single");
          this.relicStock.onSelectionChange = (selection: Card[]) => {
            if (selection.length > 0) {
              this.cardManager.getCardElement(selection[0]).appendChild(document.getElementById("relic-card-commands"));

              deselectDice();
              deselectPyramidCards();
            } else {
              document.getElementById("table").appendChild(document.getElementById("relic-card-commands"));
            }
          };

          // Pyramid Button
          (this as any).statusBar.addActionButton(
            _("Complete Room"),
            () => {
              let selectedCard = this.pyramidStock.getSelection()[0];
              (this as any).bgaPerformAction("actTakeRoom", {
                cardId: selectedCard.id,
              });
            },
            {
              id: "btnTakeRoom",
              color: "primary",
              classes: "",
              destination: document.getElementById("pyramid-card-commands"),
            }
          );

          // Relic Buttons
          (this as any).statusBar.addActionButton(_("Used"), () => { }, {
            id: "btnUsed",
            color: "gray",
            classes: "disabled",
            destination: document.getElementById("relic-card-commands"),
          });
          (this as any).statusBar.addActionButton(
            _("Use Relic"),
            () => {
              let selectedCard = this.relicStock.getSelection()[0];
              (this as any)
                .bgaPerformAction("actPlayRelic", {
                  cardId: selectedCard.id,
                })
                ?.then(() => document.getElementById("relics").classList.remove("expanded"));
            },
            {
              id: "btnUseRelic",
              color: "primary",
              classes: "",
              destination: document.getElementById("relic-card-commands"),
            }
          );
          (this as any).statusBar.addActionButton(
            _("Discard for new turn"),
            () => {
              let selectedCard = this.relicStock.getSelection()[0];
              (this as any)
                .bgaPerformAction("actTurnDiscardRelic", {
                  cardId: selectedCard.id,
                })
                ?.then(() => document.getElementById("relics").classList.remove("expanded"));
            },
            {
              id: "btnDiscardRelicForNewTurn",
              color: "primary",
              classes: "",
              destination: document.getElementById("relic-card-commands"),
            }
          );
          break;
        }
        case "playerPlayingRelic": {
          // cancel button
          (this as any).statusBar.addActionButton(
            _("Cancel"),
            () => (this as any).bgaPerformAction("actCancelPlayingRelic", {}),
            { id: "btnCancel", color: "secondary" }
          );

          if (args.diceCount != null && Math.max(...args.diceCount) > 1) {
            document.getElementById("dice-box-header").innerText = dojo.string.substitute(
              _("Select ${diceCount} dice"),
              {
                diceCount: args.diceCount.join("/"),
              }
            );
          }

          let dice = this.getDice();
          let selectableDiceIds = args.diceValues
            ? dice.filter((d) => args.diceValues.includes(d.value)).map((d) => d.id)
            : [0, 1, 2, 3, 4];

          // Dice
          const onSelectionChangedForRelic = (selectedDice) => {
            let enableCmd = false;
            if (args.diceCount == null || args.diceCount.length == 0) {
              enableCmd = selectedDice.length > 0;
            } else if (args.diceCount.includes(selectedDice.length)) {
              enableCmd = true;
            }
            let canTurnUp = !selectedDice.find((d) => d.value == 6);
            let canTurnDown = !selectedDice.find((d) => d.value == 1);
            document.querySelectorAll(".btn-play-relic-on-dice").forEach((el) => {
              if (el.id == "btnPlayRelicTurnUp") {
                el.classList.toggle("disabled", !(enableCmd && canTurnUp));
              } else if (el.id == "btnPlayRelicTurnDown") {
                el.classList.toggle("disabled", !(enableCmd && canTurnDown));
              } else {
                el.classList.toggle("disabled", !enableCmd);
              }
            });
          };
          this.setDiceSelectable(
            {
              ids: selectableDiceIds,
              count: args.diceCount == null ? null : Math.max(...args.diceCount),
            },
            onSelectionChangedForRelic
          );
          setTimeout(() => onSelectionChangedForRelic(this.getSelectedDice()), 100);

          document.getElementById("dice-box").dataset["action"] = args.actionName;

          if (args.actionName == "turnup" || args.actionName == "turnupdown") {
            (this as any).statusBar.addActionButton(
              _("Add 1"),
              () => {
                let selectedDice = this.getSelectedDice();
                selectedDice.forEach((d) => this.getDiceElement(d.id)?.classList.add("rerolling"));
                setTimeout(
                  () =>
                    (this as any).bgaPerformAction("actPlayRelicOnDice", {
                      diceIds: selectedDice.map((d) => d.id).join(","),
                      actionName: "turnup",
                    }),
                  500
                );
              },
              {
                id: "btnPlayRelicTurnUp",
                color: "primary",
                classes: "disabled btn-play-relic-on-dice",
                destination: document.getElementById("dice-box-commands"),
              }
            );
          }
          if (args.actionName == "turndown" || args.actionName == "turnupdown") {
            (this as any).statusBar.addActionButton(
              _("Subtract 1"),
              () => {
                let selectedDice = this.getSelectedDice();
                selectedDice.forEach((d) => this.getDiceElement(d.id)?.classList.add("rerolling"));
                setTimeout(
                  () =>
                    (this as any).bgaPerformAction("actPlayRelicOnDice", {
                      diceIds: selectedDice.map((d) => d.id).join(","),
                      actionName: "turndown",
                    }),
                  500
                );
              },
              {
                id: "btnPlayRelicTurnDown",
                color: "primary",
                classes: "disabled btn-play-relic-on-dice",
                destination: document.getElementById("dice-box-commands"),
              }
            );
          }
          if (!args.actionName.startsWith("turn")) {
            let actionName = _("Submit");
            switch (args.actionName) {
              case "flip":
                actionName = _("Flip");
                break;
              case "reroll":
                actionName = _("Reroll");
                break;
            }
            // Relic action button
            (this as any).statusBar.addActionButton(
              actionName,
              () => {
                let selectedDice = this.getSelectedDice();
                selectedDice.forEach((d) => this.getDiceElement(d.id)?.classList.add("rerolling"));
                setTimeout(
                  () =>
                    (this as any).bgaPerformAction("actPlayRelicOnDice", {
                      diceIds: selectedDice.map((d) => d.id).join(","),
                      actionName: args.actionName,
                    }),
                  500
                );
              },
              {
                id: "btnPlayRelicOnDice",
                color: "primary",
                classes: "disabled btn-play-relic-on-dice",
                destination: document.getElementById("dice-box-commands"),
              }
            );
          }
          break;
        }
        case "playerDiscardRelic": {
          document.getElementById("relics").classList.add("expanded");

          // Relic Button
          (this as any).statusBar.addActionButton(
            _("Discard"),
            () => {
              let selectedCard = this.relicStock.getSelection()[0];
              (this as any)
                .bgaPerformAction("actDiscardRelic", {
                  cardId: selectedCard.id,
                })
                ?.then(() => document.getElementById("relics").classList.remove("expanded"));
            },
            {
              id: "btnDiscardRelic",
              color: "primary",
              classes: "",
              destination: document.getElementById("relic-card-commands"),
            }
          );

          // Relics
          this.relicStock.setSelectionMode("single");
          this.relicStock.onSelectionChange = (selection: Card[]) => {
            if (selection.length > 0) {
              this.cardManager.getCardElement(selection[0]).appendChild(document.getElementById("relic-card-commands"));

              deselectDice();
              deselectPyramidCards();
            } else {
              document.getElementById("table").appendChild(document.getElementById("relic-card-commands"));
            }
          };
        }
      }
    } else if (!this.isReadOnly()) {
      switch (stateName) {
        case "playCard": {
          break;
        }
      }
    }
  }

  ///////////////////////////////////////////////////
  //// Utility methods

  /*
    
        Here, you can defines some utility methods that you can use everywhere in your javascript
        script.
    
    */

  public isReadOnly() {
    return (this as any).isSpectator || typeof g_replayFrom != "undefined" || g_archive_mode;
  }

  public enableDefeatButton(enabled = true) {
    document.getElementById("btnGiveUp")?.classList.toggle("disabled", !enabled);
  }

  public setDiceValues(dice: Record<number, Die>) {
    for (let i = 0; i < 5; i++) {
      const slot = document.querySelector(`#dice-box [data-slot-id="${i}"]`);
      let val = Number(slot.getAttribute("data-die-value"));

      if (val != dice[i].value) {
        if (slot.classList.contains("rerolling")) {
          slot.classList.remove("rerolling");
          slot.setAttribute("data-die-value", dice[i]?.value?.toString() || "0");
        } else {
          slot.classList.add("roll-result");
          setTimeout(() => slot.setAttribute("data-die-value", dice[i]?.value?.toString() || "0"), 500);
          setTimeout(() => slot.classList.remove("roll-result"), 1000);
        }
      }
      if (slot.classList.contains("rerolling")) {
        slot.classList.remove("rerolling");
      }
    }
  }

  public getSelectedDice(): Die[] {
    return Array.from(document.querySelectorAll(`#dice-box .slot.selected`)).map((el) => ({
      id: Number(el.getAttribute("data-slot-id")),
      value: Number(el.getAttribute("data-die-value")),
    }));
  }
  public getDice(): Die[] {
    return Array.from(document.querySelectorAll(`#dice-box .slot`)).map((el: HTMLElement) => ({
      id: Number(el.dataset.slotId),
      value: Number(el.dataset.dieValue),
    }));
  }
  public getDiceElement(dieId: number) {
    return document.querySelector(`#dice-box .slot[data-slot-id="${dieId}"]`);
  }
  public clearSelectedDice(silent: boolean = false) {
    Array.from(document.querySelectorAll(`#dice-box .slot.selected`)).forEach((el: HTMLElement) =>
      silent ? el.classList.remove("selected") : el.click()
    );
  }

  public disableDiceSelection() {
    if ((this as any).isSpectator) return;

    document.querySelectorAll("#dice-box .dice-slot").forEach((el) => {
      el.classList.remove("selectable");
      el.classList.remove("selected");
      el.classList.add("disabled");
    });
  }
  public setDiceSelectable(
    options: { ids?: number[]; count?: number },
    onSelectionChanged?: (selectedDice: Die[]) => void
  ) {
    if (options.ids == null) options.ids = [0, 1, 2, 3, 4];
    const setSelectable = (el: Element, selectable: boolean = true) => {
      el.classList.toggle("selectable", selectable);
      el.classList.toggle("disabled", !selectable);
    };
    const setDefaultSelectable = (deselectAll: boolean = false) => {
      for (let i = 0; i < 5; i++) {
        const slot = document.querySelector(`#dice-box [data-slot-id="${i}"]`);
        setSelectable(slot, options.ids.includes(i));
        deselectAll && slot.classList.toggle("selected", options.count == 5);
      }
    };
    setDefaultSelectable(true);
    (document.querySelector(`#dice-box`) as HTMLDivElement).onclick = (e) => {
      const slot = (e.target as HTMLElement).closest(".slot");
      if (slot?.classList.contains("selectable")) {
        if (options.count == 1) {
          if (!slot.classList.contains("selected")) {
            document.querySelectorAll(`#dice-box .slot.selected`).forEach((el) => el.classList.remove("selected"));
          }
        }

        slot.classList.toggle("selected");

        let selectedDice = this.getSelectedDice();
        if (options.count > 1 && selectedDice.length >= options.count) {
          document.querySelectorAll(`#dice-box .slot:not(.selected)`).forEach((el) => setSelectable(el, false));
        } else {
          setDefaultSelectable();
        }
        onSelectionChanged?.(this.getSelectedDice());
      }
    };
  }

  ///////////////////////////////////////////////////
  //// Player's action

  /*
    
        Here, you are defining methods to handle player's action (ex: results of mouse click on 
        game objects).
        
        Most of the time, these methods:
        _ check the action is possible at this game state.
        _ make a call to the game server
    
    */

  ///////////////////////////////////////////////////
  //// Reaction to cometD notifications

  /*
        setupNotifications:
        
        In this method, you associate each of your game notifications with your local method to handle it.
        
        Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                your dicepyramid.game.php file.
    
    */
  setupNotifications() {
    console.log("notifications subscriptions setup");

    dojo.subscribe("pyramidSetup", this, "notif_pyramidSetup");
    dojo.subscribe("reRoll", this, "notif_reRoll");
    dojo.subscribe("initialRoll", this, "notif_initialRoll");
    dojo.subscribe("takeRoom", this, "notif_takeRoom");
    dojo.subscribe("playRelic", this, "notif_playRelic");
    dojo.subscribe("playPowerRelic", this, "notif_playPowerRelic");
    dojo.subscribe("playingRelic", this, "notif_playingRelic");
    dojo.subscribe("cancelPlayingRelic", this, "notif_cancelPlayingRelic");
    dojo.subscribe("discardRelic", this, "notif_discardRelic");
  }

  notif_pyramidSetup(e) {
    console.log("notif_pyramidSetup", e);

    this.pyramidStock.addCards(e.args.cards);
  }
  notif_initialRoll(e) {
    console.log("notif_initialRoll", e);

    this.notif_reRoll(e);
  }
  notif_reRoll(e) {
    console.log("notif_reRoll", e);

    this.clearSelectedDice();
    this.setDiceValues(e.args.dice);
    this.pyramidStock.setSelectableCards(e.args.takeableCards);

    let btn = document.getElementById("btnRerollSelectedDice");
    if (btn) {
      btn.innerText = dojo.string.substitute(_("Re-roll (${rerolls_remaining} roll(s) left)"), {
        rerolls_remaining: e.args.rerolls,
      });
    }
    if (!e.args.rerolls) {
      this.disableDiceSelection();
    }

    this.enableDefeatButton(!e.args.rerolls && this.relicStock.getCards().length == 0 && !e.args.takeableCards?.length);
  }
  notif_takeRoom(e) {
    console.log("notif_takeRoom", e);

    this.relicStock.addCard(e.args.card);
    for (let card of e.args.flippedCards) {
      this.pyramidStock.setCardVisible(card, true);
    }
  }
  notif_playRelic(e) {
    console.log("notif_playRelic", e);

    if (e.args.dice) {
      this.clearSelectedDice();
      this.setDiceValues(e.args.dice);
    }
    if (e.args.takeableCards) {
      this.pyramidStock.setSelectableCards(e.args.takeableCards);
    }
    if (e.args.relicCard) {
      try {
        if (e.args.powerRelic) {
          this.relicStock.addCard(e.args.relicCard);
        } else {
          // document
          //   .getElementById("table")
          //   .appendChild(document.getElementById("relic-card-commands"));
          this.discardStock.addCard(e.args.relicCard);
        }
      } catch {
        /* ignore errors here */
      }
    }
  }
  notif_playPowerRelic(e) {
    console.log("notif_playPowerRelic", e);
    e.args.powerRelic = true;
    this.notif_playRelic(e);
  }
  notif_discardRelic(e) {
    console.log("notif_discardRelic", e);

    try {
      this.discardStock.addCard(e.args.relicCard);
    } catch {
      /* ignore errors here */
    }
  }
  notif_playingRelic(e) {
    console.log("notif_playingRelic", e);
    this.playingRelicStock.addCard(e.args.relicCard);
  }
  notif_cancelPlayingRelic(e) {
    console.log("notif_cancelPlayingRelic", e);
    this.relicStock.addCard(e.args.relicCard);
  }
}
