const jstpl_CardHelpDialog = (card: Card, cardInfo: ICardInfo) => `
  <div class="card-help">
    <!-- <div class="card-help-header">${_(cardInfo.name)}</div> -->
    <div class="card-help-content">
        <div class="room">
            <div class="image" style="background-position: ${((card.type - 1) * 100) / 15
  }% ${((card.type - 1) * 2 * 100) / 1}%;"></div>
            <div class="text">
                <div class="card-name">${_(cardInfo.room.name)}</div>
                <div class="card-effect">${_(cardInfo.room.text)}</div>
            </div>
        </div>
        <div class="relic">
            <div class="image" style="background-position: ${((card.type - 1) * 100) / 15
  }% ${(((card.type - 1) * 2 + 1) * 100) / 1}%;"></div>
            <div class="text">
                <div class="card-name">${_(cardInfo.relic.name)}</div>
                <div class="card-effect">${_(cardInfo.relic.text)}</div>
            </div>
        </div>
    </div>
  </div>`;

interface ICardEndInfo {
  name: string;
  text: string;
}
interface ICardInfo {
  name: string;

  room: ICardEndInfo;
  relic: ICardEndInfo;
}
