import { LitElement, html } from 'lit'
import { classMap } from 'lit/directives/class-map.js'

class MyDialog extends LitElement {

  constructor () {
    super()
    this.opened = false
  }
  createRenderRoot(){
    return this;
  }

  static get properties () {
    return {
      opened: {type: Boolean}
    }
  }

  render () {
    return html`
<style>
.opened {
    display: flex;
}
.closed {
    display: none;
}
.dialog {
    flex-direction: column;
    border: 2px outset black;
    padding: 1em;
    margin: 1em;
}
.buttons {
    display: flex;
    flex-direction: row;
}
.accept {
    justify-content: space-around;
    align-content: space-around;
}
.cancel {
    justify-content: space-around;
    align-content: space-around;
}
</style>
<div class="${classMap({dialog: true, opened: !this.opened, closed: this.opened})}">
    <h1 class="title ">Title</h1>
    <p class="content">This is a dialog</p>
    <div class="buttons">
      <button class="accept" @click="${() => this.dispatchEvent(new CustomEvent('dialog.accept'))}">Ok</button>
      <button class="cancel" @click="${() => this.dispatchEvent(new CustomEvent('dialog.cancel'))}">Cancel</button>    
    </div>
</div>`
  }
}

customElements.define('my-dialog', MyDialog)