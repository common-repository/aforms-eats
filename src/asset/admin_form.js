import { h, app } from 'hyperapp';
import Ajv from "ajv";
import Sortable from 'sortablejs';
import { createMessages, updateMessages, translate, deepCopy, Message, focusErrorInput, lmove, linsert, lremove, lreplace, findByProp, findIndexByProp, branchNo, tnOnCreate, tnOnRemove, joinSet, mapHash, scrollToTop, sprintf } from './admin_common';

const submit = (form, k) => {
  const url = submitUrl.replace('placeholder', form.id)
  jQuery.ajax({
    type: "post", 
    url: url, 
    data: JSON.stringify(form), 
    contentType: 'application/json',
    success: function(response) {
      k(response)
    }, 
    dataType: 'json'
  });
}


const selectImage = (name, k) => {
  const modal = wp.media({
    title: _T('Select Image'), 
    library: {type:'image'}, 
    button: {text:_T('OK')}, 
    multiple: false
  });

  modal.on('select', (y) => {
    const image = modal.state().get('selection').first().toJSON();
    k({name, image})
  });
  modal.open()
}


const instantiateSortable = (
    {
      group, 
      handle, 
      onSortStart, 
      onSortEnd, 
      ...options 
    }
) => {
  var instance = null;
  var marker = null;
  const onStart = (ev) => {
    marker = ev.item.nextElementSibling
    onSortStart({
      id: ev.from.id, 
      index: ev.oldIndex
    })
  }
  const onEnd = (ev) => {
    setTimeout(function() {
      ev.from.insertBefore(ev.item, marker)
      marker = null
    }, 0)
    onSortEnd({
      fromId: ev.from.id, 
      fromIndex: ev.oldIndex, 
      toId: ev.to.id, 
      toIndex: ev.newIndex
    })
  }
  const effectiveOptions = {
    ...options, 
    group, 
    handle, 
    onStart, 
    onEnd, 
    ghostClass: "wq-is-ghost", 
    animation: 150
  }
  return {
    oncreate: (el) => {
      instance = Sortable.create(el, effectiveOptions)
      //tnOnCreate(el)
    }, 
    onremove: null, //tnOnRemove, 
    ondestroy: () => {
      if (instance) {
        instance.destroy()
        instance = null
      }
    }
  }
}
const SortableList = (
    {
      id, 
      group, 
      handle, 
      onSortStart, 
      onSortEnd, 
      collapsed = false, 
      isRoot = false
    }, children) => {
  const {oncreate, onremove, ondestroy} = instantiateSortable({group, handle, onSortStart, onSortEnd}) 
  const style = isRoot ? {} : {'max-height':children.length*40+"px"}
  return (
    <div class={`wq-SortableList ${collapsed ? 'wq-is-collapsed' : ''} ${isRoot ? 'wq-is-root' : ''}`} id={id} key={id} style={style} oncreate={oncreate} ondestroy={ondestroy}>
      {children}
    </div>
  )
}

const Image = (
    {
      src, 
      scaling = 'center', 
      alt = ''
    }) => {
  const style = {backgroundImage: "url("+src+")"}
  return (
    <div class={`wq-Image wq-scaling-${scaling}`} style={style}>
      <img src={src} alt={alt} class="wq--img" />
    </div> 
  )
}

const ImageInput = (
    {
      name, 
      src, 
      onimageselect
    }) => {
  const open = () => {
    selectImage(name, onimageselect)
  }
  const clear = () => {
    onimageselect({name, image:null})
  }
  return (
    <div class="wq-ImageInput">
      <Image src={src || noimageUrl} />
      <button type="button" class="button" onclick={open}>{_T('Open Media')}</button>
      <button type="button" class="button" onclick={clear} disabled={!src}>{_T('Clear')}</button>
    </div>
  )
}

const Control = (
    {
      label, 
      required, 
      message, 
      key = null, 
      note
    }, children) => {
  return (
    <tr key={key}>
      <th scope="row">
        <label>{label}</label>
        <Message>{message}</Message>
      </th>
      <td>
        {children}
        <p class="description">{note}</p>
      </td>
    </tr>
  )
}

const IconButton = (
    {
      icon, 
      title, 
      onclick, 
      appearance = 'normal',  // danger, primary
      disabled = false, 
      xclass = '', 
      ...props
    }) => {
  return (
    <button type="button" class={`wq-IconButton wq-appearance-${appearance} ${xclass}`} onclick={onclick} disabled={disabled} title={title} {...props}>
      <i class="material-icons">{icon}</i>
    </button>
  )
}

// general tab
const general = {
  createInitialState: (form) => {
    const general = {title: form.title, navigator: form.navigator, doConfirm: form.doConfirm, thanksUrl: form.thanksUrl}
    return {
      formId: form.id, 
      general, 
      form: general, 
      messages: {}
    }
  }, 
  actions: {
    onchange: (ev) => ({form, ...rest}, actions) => {
      const doConfirm = ev.currentTarget.value == "true"
      return {...rest, form:{...form, doConfirm}}
    }, 
    onenumchange: (ev) => ({form, ...rest}, actions) => {
      const name = ev.currentTarget.name
      const val = ev.currentTarget.value
      return {...rest, form:{...form, [name]:val}}
    }, 
    oninput: (ev) => ({form, ...rest}, actions) => {
      return {form:{...form, [ev.currentTarget.name]:ev.currentTarget.value}, ...rest}
    }, 
    onblur: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      const path = '.'+ev.currentTarget.name
      const v = validate.General
      v(copy)
      messages = updateMessages(messages, path, v.errors)
      return {messages, ...rest}
    }, 
    onSettle: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      const v = validate.General
      if (! v(copy)) {
        messages = createMessages(v.errors)
        focusErrorInput(v.errors)
        return {...rest, messages}
      }
      window.requestAnimationFrame(() => {
        allActions.showNotice('Changes committed. Be sure to save data before moving to another page.')
      })
      return {...rest, messages:{}, general:copy}
    }, 
    onDismiss: (ev) => (state, actions) => {
      return {...state, messages:{}, form:state.general}
    }, 
    updateId: (id) => (state, actions) => {
      return {...state, formId:id}
    }
  }, 
  view: (state, actions) => {
    return (
      <form novalidate key="form-general" id="form-general">
        <table class="form-table">
          <tbody>
            {state.formId > 0 ? (
              <Control label={_T('Shortcode')} note={_T('Embed the shortcode above in a post or a page to display this.')}>
                <input type="text" readonly value={`[aforms-eats-form id="${state.formId}"]`} class="large-text" />
              </Control>
            ) : null}
            <Control label={_T('Title')} note={_T('The name for you to distinguish the form. The end-users don\'t see this.')} message={_T(state.messages['.title'])}>
              <input type="text" name="title" value={""+state.form.title} class="regular-text" oninput={actions.oninput} onblur={actions.onblur} />
            </Control>
            <Control label={_T('Display Confirmation Screen')} message={_T(state.messages['.doConfirm'])}>
              <fieldset>
                <label><input type="radio" name="doConfirm" value="true" checked={state.form.doConfirm} onchange={actions.onchange} /><span>{_T('Display')}</span></label>
                <label><input type="radio" name="doConfirm" value="false" checked={!state.form.doConfirm} onchange={actions.onchange} /><span>{_T('Don\'t Display')}</span></label>
              </fieldset>
            </Control>
            <Control label={_T('Thanks Url')} message={_T(state.messages['.thanksUrl'])} note={_T('If you want to display another page after submitting the form, enter the URL.')}>
              <input type="text" name="thanksUrl" value={state.form.thanksUrl} class="large-text" oninput={actions.oninput} onblur={actions.onblur} />
            </Control>
          </tbody>
        </table>
        <p class="submit">
          <button type="button" class="button button-primary" onclick={actions.onSettle}>{_T('Commit Changes')}</button>
          <button type="button" class="button" onclick={actions.onDismiss}>{_T('Discard Changes')}</button>
        </p>
      </form>
    )
  }
}

const priceCheckerEnum = [{v:'equal',l:'Equal'},{v:'notEqual',l:'Not Equal'},{v:'greaterThan',l:'Greater Than'},{v:'greaterEqual',l:'Greater Equal'},{v:'lessThan',l:'Less Than'},{v:'lessEqual',l:'Less Equal'}]

const listQuantities = (id, items) => {
  const idx = findIndexByProp("id", id, items)
  return items.slice(0, idx).filter((item) => item.type == "Quantity")
}

// Details tab
const details = {
  createInitialState: (form) => {
    const productIds = (cur, item) => {
      if (item.type == "Group") {
        return [...cur, ...item.products.map(p => p.id)]
      } else {
        return cur
      }
    }
    const collapsing = form.detailItems.reduce((cur, item) => {
      if (item.type == 'Group') {
        cur[item.id] = true
        return cur
      } else {
        return cur
      }
    }, {})
    return {
      items: form.detailItems, 
      path: [], 
      form: null, 
      messages: {}, 
      sorting: null, 
      collapsing, 
      nextItemId: Math.max(0, ...form.detailItems.map(x => x.id)) + 1, 
      nextProductId: Math.max(0, ...form.detailItems.reduce(productIds, [])) + 1, 
      spShowNav: true, 
      popping: false
    }
  }, 
  actions: {
    onShowNav: (ev) => (state, actions) => {
      return {...state, spShowNav:true}
    }, 
    onHideNav: (ev) => (state, actions) => {
      return {...state, spShowNav:false}
    }, 
    onDuplicate: (ev) => (state, actions) => {
      if (state.form.type != "Product") {
        const idx = findIndexByProp("id", state.path[0], state.items)
        const item = deepCopy(state.items[idx])
        let nextItemId = state.nextItemId
        item.id = nextItemId++
        item.name = sprintf(_T('%s\'s Copy'), item.name)
        let nextProductId = state.nextProductId
        if (state.form.type == "Group") {
          item.products.forEach(product => {
            product.id = nextProductId++
          })
        }
        return {...state, nextItemId, nextProductId, items:linsert(state.items, idx + 1, item), form:item, path:[item.id], messages:{}}
      } else { // Product
        const idx0 = findIndexByProp("id", state.path[0], state.items)
        const idx1 = findIndexByProp("id", state.path[1], state.items[idx0].products)
        const product = deepCopy(state.items[idx0].products[idx1])
        let nextProductId = state.nextProductId
        product.id = nextProductId++
        product.name = sprintf(_T('%s\'s Copy'), product.name)
        const item = {...state.items[idx0], products:linsert(state.items[idx0].products, idx1 + 1, product)}
        return {...state, nextProductId, items:lreplace(state.items, idx0, item), form:product, path:[item.id, product.id], messages:{}}
      }
    }, 
    onDelete: (ev) => (state, actions) => {
      if (state.form.type == "Product") {
        const idx0 = findIndexByProp("id", state.path[0], state.items)
        const idx1 = findIndexByProp("id", state.path[1], state.items[idx0].products)
        const item = {...state.items[idx0], products:lremove(state.items[idx0].products, idx1)}
        return {...state, items:lreplace(state.items, idx0, item), form:null, path:[], messages:{}}
      } else {
        const idx = findIndexByProp("id", state.path[0], state.items)
        return {...state, items:lremove(state.items, idx), form:null, path:[], messages:{}}
      }
    }, 
    onAddAuto: (ev) => (state, actions) => {
      const idx = (state.form) ? findIndexByProp("id", state.path[0], state.items) + 1 : 0
      let nextItemId = state.nextItemId
      const item = {id:nextItemId++, type:"Auto", category:"", name:_T('New Auto Item'), price:100, taxRate:null, depends:"", quantity:-1}
      return {...state, items:linsert(state.items, idx, item), nextItemId, form:item, path:[item.id], messages:{}}
    }, 
    onAddGroup: (ev) => (state, actions) => {
      const idx = (state.form) ? findIndexByProp("id", state.path[0], state.items) + 1 : 0
      let nextItemId = state.nextItemId
      const item = {id:nextItemId++, type:"Group", image:"", name:_T('New Group'), visible:true, note:"", products:[]}
      return {...state, items:linsert(state.items, idx, item), nextItemId, form:item, path:[item.id], messages:{}}
    }, 
    onAddProduct: (ev) => (state, actions) => {
      const idx0 = findIndexByProp("id", state.path[0], state.items)
      const idx1 = state.path.length == 1 ? 0 : findIndexByProp("id", state.path[1], state.items[idx0].products) + 1
      let nextProductId = state.nextProductId
      const product = {id:nextProductId++, type:"Product", image:"", name:_T('New Product'), state:'effective', note:"", price:100, taxRate:null, ribbons:{}}
      const item = {...state.items[idx0], products:linsert(state.items[idx0].products, idx1, product)}
      return {...state, items:lreplace(state.items, idx0, item), nextProductId, form:product, path:[item.id, product.id], messages:{}}
    }, 
    onAddPriceWatcher: (ev) => (state, actions) => {
      const idx = (state.form) ? findIndexByProp("id", state.path[0], state.items) + 1 : 0
      let nextItemId = state.nextItemId
      const item = {id:nextItemId++, type:"PriceWatcher", lower:null, lowerIncluded:false, higher:null, higherIncluded:false, labels:""}
      return {...state, items:linsert(state.items, idx, item), nextItemId, form:item, path:[item.id], messages:{}}
    }, 
    onAddStop: (ev) => (state, actions) => {
      const idx = (state.form) ? findIndexByProp("id", state.path[0], state.items) + 1 : 0
      let nextItemId = state.nextItemId
      const item = {id:nextItemId++, type:"Stop", message:"Error", depends:""}
      return {...state, items:linsert(state.items, idx, item), nextItemId, form:item, path:[item.id], messages:{}}
    }, 
    onOuterSortStart: ({id:_id, index}) => (state, actions) => {
      return {...state, sorting:"outer"}
    }, 
    onOuterSortEnd: ({fromId, fromIndex, toId:_toId, toIndex}) => (state, actions) => {
      return {...state, sorting:null, items:lmove(state.items, fromIndex, toIndex)}
    }, 
    onInnerSortStart: ({id, index}) => (state, actions) => {
      return {...state, sorting:'inner'}
    }, 
    onInnerSortEnd: ({fromId, fromIndex, toId, toIndex}) => (state, actions) => {
      const from = findIndexByProp("id", branchNo(fromId, '-'), state.items)
      const to = findIndexByProp("id", branchNo(toId, '-'), state.items)
      if (from != to) {
        const product = state.items[from].products[fromIndex]
        const fromGroup = {...state.items[from], products:lremove(state.items[from].products, fromIndex)}
        const toGroup = {...state.items[to], products:linsert(state.items[to].products, toIndex, product)}
        return {
          ...state, 
          sorting:null, 
          items: lreplace(lreplace(state.items, from, fromGroup), to, toGroup)
        }
      } else {
        const group = {...state.items[from], products:lmove(state.items[from].products, fromIndex, toIndex)}
        return {
          ...state, 
          sorting:null, 
          items: lreplace(state.items, from, group)
        }
      }
    }, 
    onOuterSelect: (ev) => (state, actions) => {
      const idx = ev.currentTarget.parentNode.id.split('-')[1]
      const item = state.items[idx]
      return {...state, path:[item.id], form:item, messages:{}}
    }, 
    onInnerSelect: (ev) => (state, actions) => {
      const indice = ev.currentTarget.parentNode.id.split('-')
      const group = state.items[indice[1]]
      const product = group.products[indice[2]]
      return {...state, path:[group.id, product.id], form:product, messages:{}}
    }, 
    onToggle: (ev) => ({collapsing, ...rest}, actions) => {
      const idx = ev.currentTarget.parentNode.id.split('-')[1]
      const id = rest.items[idx].id
      if (collapsing.hasOwnProperty(id)) {
        collapsing = {...collapsing}
        delete collapsing[id]
        return {collapsing, ...rest}
      } else {
        return {collapsing:{...collapsing, [id]:true}, ...rest}
      }
    }, 
    onImageSelect: ({name, image}) => ({form, ...rest}, actions) => {
      return {...rest, form:{...form, [name]:image ? image.url : null}}
    }, 
    onInput: (ev) => ({form, ...rest}, actions) => {
      return {...rest, form:{...form, [ev.currentTarget.name]:ev.currentTarget.value}}
    }, 
    onGroupBlur: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      const path = '.'+ev.currentTarget.name;
      validate.Group(copy)
      messages = updateMessages(messages, path, validate.Group.errors)
      return {messages:messages, ...rest}
    }, 
    onAutoBlur: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      const path = '.'+ev.currentTarget.name;
      validate.Auto(copy)
      messages = updateMessages(messages, path, validate.Auto.errors)
      return {messages:messages, ...rest}
    }, 
    onProductBlur: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      const path = '.'+ev.currentTarget.name;
      validate.Product(copy)
      messages = updateMessages(messages, path, validate.Product.errors)
      return {messages:messages, ...rest}
    }, 
    onPriceWatcherBlur: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      const path = '.'+ev.currentTarget.name
      validate.PriceWatcher(copy)
      messages = updateMessages(messages, path, validate.PriceWatcher.errors)
      return {messages, ...rest}
    }, 
    onStopBlur: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      const path = '.'+ev.currentTarget.name
      validate.Stop(copy)
      messages = updateMessages(messages, path, validate.Stop.errors)
      return {messages, ...rest}
    }, 
    onChange: (ev) => ({form, ...rest}, actions) => {
      const value = ev.currentTarget.value == "true"
      return {...rest, form:{...form, [ev.currentTarget.name]:value}}
    }, 
    onChangeValue: (ev) => ({form, ...rest}, actions) => {
      const value = ev.currentTarget.value
      return {...rest, form:{...form, [ev.currentTarget.name]:value}}
    }, 
    onRibbonChange: (ev) => ({form, ...rest}, actions) => {
      const name = ev.currentTarget.name
      var ribbons = {...form.ribbons}
      if (ev.currentTarget.checked) {
        ribbons[name] = true
      } else {
        delete ribbons[name]
      }
      form = {...form, ribbons}
      return {...rest, form}
    }, 
    onGroupSettle: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      const validator = validate[copy.type]
      if (! validator(copy)) {
        const messages = createMessages(validator.errors)
        focusErrorInput(validator.errors)
        return {...rest, messages}
      }
      const idx = findIndexByProp("id", rest.path[0], rest.items)
      const items = lreplace(rest.items, idx, copy)
      return {...rest, messages:{}, form:null, path:[], items, spShowNav:true}
    }, 
    onAutoSettle: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      if (! validate.Auto(copy)) {
        const messages = createMessages(validate.Auto.errors)
        focusErrorInput(validate.Auto.errors)
        return {...rest, messages}
      }
      const idx = findIndexByProp("id", rest.path[0], rest.items)
      const items = lreplace(rest.items, idx, copy)
      return {...rest, messages:{}, form:null, path:[], items, spShowNav:true}
    }, 
    onProductSettle: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      const validator = validate[copy.type]
      if (! validator(copy)) {
        const messages = createMessages(validator.errors)
        focusErrorInput(validator.errors)
        return {...rest, messages}
      }
      const idx0 = findIndexByProp("id", rest.path[0], rest.items)
      const idx1 = findIndexByProp("id", rest.path[1], rest.items[idx0].products)
      const products = lreplace(rest.items[idx0].products, idx1, copy)
      const item = {...rest.items[idx0], products}
      const items = lreplace(rest.items, idx0, item)
      return {...rest, messages:{}, form:null, path:[], items, spShowNav:true}
    }, 
    onPriceWatcherSettle: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      if (! validate.PriceWatcher(copy)) {
        const messages = createMessages(validate.PriceWatcher.errors)
        focusErrorInput(validate.PriceWatcher.errors)
        return {...rest, messages}
      }
      const idx = findIndexByProp("id", rest.path[0], rest.items)
      const items = lreplace(rest.items, idx, copy)
      return {...rest, messages:{}, form:null, path:[], items, spShowNav:true}
    }, 
    onStopSettle: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      if (! validate.Stop(copy)) {
        const messages = createMessages(validate.Stop.errors)
        focusErrorInput(validate.Stop.errors)
        return {...rest, messages}
      }
      const idx = findIndexByProp("id", rest.path[0], rest.items)
      const items = lreplace(rest.items, idx, copy)
      return {...rest, messages:{}, form:null, path:[], items, spShowNav:true}
    }, 
    onDismiss: (ev) => (state, actions) => {
      return {...state, messages:{}, form:null, path:[], spShowNav:true}
    }, 
    onOpenExtra: (ev) => (state, actions) => {
      return {...state, popping:true}
    }, 
    onCloseExtra: (ev) => (state, actions) => {
      return {...state, popping:false}
    }
  }, 
  view: (state, actions) => {
    return (
      <div class="wq-Details">
        <div class={`wq--nav ${state.spShowNav ? 'wq-is-spshown' : ''}`}>
          <div class="wq--tools">
            <IconButton icon={state.popping ? "expand_less" : "expand_more"} title={_T('Additional Menu')} onclick={state.popping ? actions.onCloseExtra : actions.onOpenExtra} />
            <IconButton icon="file_copy" title={_T('Duplicate')} onclick={actions.onDuplicate} disabled={!state.form} />
            <IconButton icon="delete" title={_T('Delete')} onclick={actions.onDelete} disabled={!state.form} />
            <div class="wq--spacer"></div>
            <IconButton icon="arrow_forward" title={_T('Close Nav')} onclick={actions.onHideNav} xclass="wq-for-close-nav" appearance={state.form ? 'primary' : 'normal'} />
          </div>
          <div class={`wq-Menu ${state.popping ? 'wq-is-open' : ''}`}>
            <button class="wq-MenuItem" onclick={actions.onAddAuto} disabled={state.form && state.form.type == "Product"}><i class="material-icons">flag</i> {_T('Auto Item')}</button>
            <button class="wq-MenuItem" onclick={actions.onAddGroup} disabled={state.form && state.form.type == "Product"}><i class="material-icons">folder</i> {_T('Group')}</button>
            <button class="wq-MenuItem" onclick={actions.onAddProduct} disabled={!state.form || (state.form.type != "Group" && state.form.type != "Product")}><i class="material-icons">description</i> {_T('Product Item')}</button>
            <button class="wq-MenuItem" onclick={actions.onAddPriceWatcher} disabled={state.form && state.form.type == "Product"}><i class="material-icons">money</i> {_T('Price Watcher')}</button>
            <button class="wq-MenuItem" onclick={actions.onAddStop} disabled={state.form && state.form.type == "Product"}><i class="material-icons">block</i> {_T('Stop')}</button>
          </div>
          <SortableList id="outer" handle=".wq--handle-outer" group='outer' onSortStart={actions.onOuterSortStart} onSortEnd={actions.onOuterSortEnd} isRoot>
            {state.items.map((item, i0) => {
              if (item.type == "Auto") {
                return (
                  <div class={`wq-ListItem wq-for-auto ${state.path.length == 1 && state.path[0] == item.id ? 'wq-is-selected' : ''} ${(!state.sorting || state.sorting == "outer") ? '' : 'wq-is-undroppable'}`} id={`item-${i0}`}>
                    <div class="wq--toggler wq-non-interactive"><i class="material-icons">arrow_drop_down</i></div>
                    <div class="wq--main" onclick={actions.onOuterSelect}>
                      <i class="material-icons">flag</i> {item.name}
                    </div>
                    <div class="wq--handle wq--handle-outer"><i class="material-icons">drag_handle</i></div>
                  </div>
                )
              } else if (item.type == "PriceWatcher") {
                return (
                  <div class={`wq-ListItem wq-for-priceWatcher ${state.path.length == 1 && state.path[0] == item.id ? 'wq-is-selected' : ''} ${(!state.sorting || state.sorting == "outer") ? '' : 'wq-is-undroppable'}`} id={`item-${i0}`}>
                    <div class="wq--toggler wq-non-interactive"><i class="material-icons">arrow_drop_down</i></div>
                    <div class="wq--main" onclick={actions.onOuterSelect}>
                      <i class="material-icons">money</i> {item.lower != null ? item.lower + ' ' + _T(findByProp('v', item.lowerIncluded ? 'lessEqual' : 'lessThan', priceCheckerEnum).l) + ' ' : ''}X{item.higher != null ? ' ' + _T(findByProp('v', item.higherIncluded ? 'lessEqual' : 'lessThan', priceCheckerEnum).l) + ' ' + item.higher : ''}
                    </div>
                    <div class="wq--handle wq--handle-outer"><i class="material-icons">drag_handle</i></div>
                  </div>
                )
              } else if (item.type == "Stop") {
                return (
                  <div class={`wq-ListItem wq-for-stop ${state.path.length == 1 && state.path[0] == item.id ? 'wq-is-selected' : ''} ${(!state.sorting || state.sorting == "outer") ? '' : 'wq-is-undroppable'}`} id={`item-${i0}`}>
                    <div class="wq--toggler wq-non-interactive"><i class="material-icons">arrow_drop_down</i></div>
                    <div class="wq--main" onclick={actions.onOuterSelect}>
                      <i class="material-icons">block</i> {item.message}
                    </div>
                    <div class="wq--handle wq--handle-outer"><i class="material-icons">drag_handle</i></div>
                  </div>
                )
              } else if (item.type == "Group") {
                return (
                  <div>
                    <div class={`wq-ListItem wq-for-group ${state.path.length == 1 && state.path[0] == item.id ? 'wq-is-selected' : ''} ${(!state.sorting || state.sorting == "outer") ? '' : 'wq-is-undroppable'} ${item.visible ? '' : 'wq-visibility-no'}`} id={`item-${i0}`}>
                      <div class={`wq--toggler ${state.collapsing.hasOwnProperty(item.id) ? 'wq-is-collapsing' : ''}`} onclick={actions.onToggle}><i class="material-icons">arrow_drop_down</i></div>
                      <div class="wq--main" onclick={actions.onOuterSelect}>
                        <i class="material-icons">folder</i> {item.name}
                      </div>
                      <div class="wq--handle wq--handle-outer"><i class="material-icons">drag_handle</i></div>
                    </div>
                    <SortableList id={`inner-${item.id}`} handle=".wq--handle-inner" group="inner" onSortStart={actions.onInnerSortStart} onSortEnd={actions.onInnerSortEnd} collapsed={state.collapsing.hasOwnProperty(item.id)}>
                      {item.products.map((product, i1) => {
                        return (
                          <div class={`wq-ListItem wq-for-product ${state.path.length == 2 && state.path[1] == product.id ? 'wq-is-selected' : ''} ${(!state.sorting || state.sorting == "inner") ? '' : 'wq-is-undroppable'} ${'wq-state-'+product.state}`} id={`product-${i0}-${i1}`}>
                            <div class="wq--toggler wq-non-interactive"><i class="material-icons">arrow_drop_down</i></div>
                            <div class="wq--main" onclick={actions.onInnerSelect}>
                              <i class="material-icons">description</i> {product.name}
                            </div>
                            <div class="wq--handle wq--handle-inner"><i class="material-icons">drag_handle</i></div>
                          </div>
                        )
                      })}
                    </SortableList>
                  </div>
                )
              }
            })}
          </SortableList>
        </div>
        <div class={`wq--form`}>
          <div class="wq--lobe">
            <IconButton icon="arrow_back" onclick={actions.onShowNav} title={_T('Open Nav')} />
          </div>
          {state.form && state.form.type == "Auto" ? (
            <form novalidate key={`form-${state.form.id}`}>
              <table class="form-table wq-x-narrow">
                <tbody>
                  <Control label={_T('Type')} note={_T('Adds a fixed detail line.')}>
                    <p>{_T('Auto Item')}</p>
                  </Control>
                  <Control label={_T('Name')} message={_T(state.messages['.name'])}>
                    <input type="text" name="name" class="regular-text" value={state.form.name} oninput={actions.onInput} onblur={actions.onAutoBlur} />
                  </Control>
                  <Control label={_T('Category')} message={_T(state.messages['.category'])} note={_T('Input here if you want to display a category name in a order detail.')}>
                    <input type="text" name="category" class="regular-text" value={state.form.category} oninput={actions.onInput} onblur={actions.onAutoBlur} />
                  </Control>
                  <Control label={_T('Price')} message={_T(state.messages['.price'])}>
                    <input type="text" name="price" class="medium-text" value={state.form.price} oninput={actions.onInput} onblur={actions.onAutoBlur} />
                  </Control>
                  <Control label={_T('Quantity')} message={_T(state.messages['.quantity'])}>
                    <select name="quantity" onchange={actions.onChangeValue}>
                      <option value={-1} selected={state.form.quantity == -1}>{_T('Fixed To 1')}</option>
                      {listQuantities(state.form.id, state.items).map((qi) => {
                        return (
                          <option value={qi.id} selected={state.form.quantity == qi.id}>{qi.name}</option>
                        )
                      })}
                    </select>
                  </Control>
                  <Control label={_T('Tax Rate')} message={_T(state.messages['.taxRate'])} note={_T('The tax rate on common settings will be applied when you leave it blank.')}>
                    <input type="text" name="taxRate" class="small-text" value={state.form.taxRate} oninput={actions.onInput} onblur={actions.onAutoBlur} /> {_T('%')}
                  </Control>
                  <Control label={_T('Required Labels')} note={_T('Separete with ",". This item is availble only if all labels listed are satisfied.')} message={_T(state.messages['.depends'])}>
                    <input type="text" name="depends" class="regular-text" value={state.form.depends} oninput={actions.onInput} onblur={actions.onAutoBlur} />
                  </Control>
                </tbody>
              </table>
              <p class="submit">
                <button type="button" class="button button-primary" onclick={actions.onAutoSettle}>{_T('Commit Changes')}</button>
                <button type="button" class="button" onclick={actions.onDismiss}>{_T('Discard Changes')}</button>
              </p>
            </form>
          ) : null}
          {state.form && state.form.type == 'PriceWatcher' ? (
            <form novalidate key={`form-${state.form.id}`}>
              <table class="form-table wq-x-narrow">
                <tbody>
                  <Control label={_T('Type')} note={_T('Monitors the estimated price and gives labels if the price is included in a spacified range.')}>
                    <p>{_T('Price Watcher')}</p>
                  </Control>
                  <Control label={_T('Lower Limit Value')} message={_T(state.messages['.lower'])} note={_T('Leave this blank if there are no lower limit.')}>
                    <input type="text" name="lower" class="medium-text" value={state.form.lower} oninput={actions.onInput} onblur={actions.onPriceWatcherBlur} />
                  </Control>
                  <Control label={_T('Includes Lower Limit Value')} message={_T(state.messages['.lowerIncluded'])}>
                    <fieldset>
                      <label><input type="radio" name="lowerIncluded" value="true" checked={state.form.lowerIncluded} onchange={actions.onChange} /><span>{_T('Include')}</span></label>
                      <label><input type="radio" name="lowerIncluded" value="false" checked={!state.form.lowerIncluded} onchange={actions.onChange} /><span>{_T("Don't Include")}</span></label>
                    </fieldset>
                  </Control>
                  <Control label={_T('Higher Limit Value')} message={_T(state.messages['.higher'])} note={_T('Leave this blank if there are no higher limit.')}>
                    <input type="text" name="higher" class="medium-text" value={state.form.higher} oninput={actions.onInput} onblur={actions.onPriceWatcherBlur} />
                  </Control>
                  <Control label={_T('Includes Higher Limit Value')} message={_T(state.messages['.higherIncluded'])}>
                    <fieldset>
                      <label><input type="radio" name="higherIncluded" value="true" checked={state.form.higherIncluded} onchange={actions.onChange} /><span>{_T('Include')}</span></label>
                      <label><input type="radio" name="higherIncluded" value="false" checked={!state.form.higherIncluded} onchange={actions.onChange} /><span>{_T("Don't Include")}</span></label>
                    </fieldset>
                  </Control>
                    <Control label={_T('Labels')} message={_T(state.messages['.labels'])} note={_T('Separate with ",". If the conditions are met, all the labels listed will be awarded.')}>
                      <input type="text" name="labels" class="regular-text" value={state.form.labels} oninput={actions.onInput} onblur={actions.onAutoBlur} />
                    </Control>
                </tbody>
              </table>
              <p class="submit">
                <button type="button" class="button button-primary" onclick={actions.onPriceWatcherSettle}>{_T('Commit Changes')}</button>
                <button type="button" class="button" onclick={actions.onDismiss}>{_T('Discard Changes')}</button>
              </p>
            </form>
          ) : null}
          {state.form && state.form.type == "Stop" ? (
            <form novalidate key={`form-${state.form.id}`}>
              <table class="form-table wq-x-narrow">
                <tbody>
                  <Control label={_T('Type')} note={_T('Stops form submission under certain conditions.')}>
                    <p>{_T('Stop')}</p>
                  </Control>
                  <Control label={_T('Message')} message={_T(state.messages['.note'])} note={_T('Appears when the form submission was stopped.')}>
                    <textarea name="message" class="large-text" rows="3" value={state.form.message} oninput={actions.onInput} onblur={actions.onStopBlur}></textarea>
                  </Control>
                  <Control label={_T('Required Labels')} note={_T('Separete with ",". Form submission is stopped if all labels listed are given.')} message={_T(state.messages['.depends'])}>
                    <input type="text" name="depends" class="regular-text" value={state.form.depends} oninput={actions.onInput} onblur={actions.onStopBlur} />
                  </Control>
                </tbody>
              </table>
              <p class="submit">
                <button type="button" class="button button-primary" onclick={actions.onStopSettle}>{_T('Commit Changes')}</button>
                <button type="button" class="button" onclick={actions.onDismiss}>{_T('Discard Changes')}</button>
              </p>
            </form>
          ) : null}
          {state.form && state.form.type == "Group" ? (
            <form novalidate key={`form-${state.form.id}`}>
              <table class="form-table wq-x-narrow">
                <tbody>
                  <Control label={_T('Type')} note={_T('Creates a group of products.')}>
                    <p>{_T('Group')}</p>
                  </Control>
                  <Control label={_T('Image')} message={_T(state.messages['.image'])}>
                    <ImageInput name="image" src={state.form.image} onimageselect={actions.onImageSelect} />
                  </Control>
                  <Control label={_T('Name')} message={_T(state.messages['.name'])}>
                    <input type="text" name="name" class="regular-text" value={state.form.name} oninput={actions.onInput} onblur={actions.onGroupBlur} />
                  </Control>
                  <Control label={_T('Visibility')} message={_T(state.messages['.visible'])}>
                    <fieldset>
                      <label><input type="radio" name="visible" value="true" checked={state.form.visible} onchange={actions.onChange} /><span>{_T('Visible')}</span></label>
                      <label><input type="radio" name="visible" value="false" checked={!state.form.visible} onchange={actions.onChange} /><span>{_T("Invisible")}</span></label>
                    </fieldset>
                  </Control>
                  <Control label={_T('Note')} message={_T(state.messages['.note'])} note={_T('You can write in HTML.')}>
                    <textarea name="note" class="large-text" rows="3" value={state.form.note} oninput={actions.onInput} onblur={actions.onGroupBlur}></textarea>
                  </Control>
                </tbody>
              </table>
              <p class="submit">
                <button type="button" class="button button-primary" onclick={actions.onGroupSettle}>{_T('Commit Changes')}</button>
                <button type="button" class="button" onclick={actions.onDismiss}>{_T('Discard Changes')}</button>
              </p>
            </form>
          ) : null}
          {state.form && state.form.type == "Product" ? (
            <form novalidate key={`form-${state.path[0]}-${state.path[1]}`}>
              <table class="form-table wq-x-narrow">
                <tbody>
                  <Control label={_T('Type')} note={_T('Adds a product that can be ordered with quantity.')}>
                    <p>{_T('Product Item')}</p>
                  </Control>
                  <Control label={_T('Image')} message={_T(state.messages['.image'])}>
                    <ImageInput name="image" src={state.form.image} onimageselect={actions.onImageSelect} />
                  </Control>
                  <Control label={_T('Name')} message={_T(state.messages['.name'])}>
                    <input type="text" name="name" class="regular-text" value={state.form.name} oninput={actions.onInput} onblur={actions.onProductBlur} />
                  </Control>
                  <Control label={_T('State')} message={_T(state.messages['.state'])}>
                    <fieldset>
                      <label><input type="radio" name="state" value="effective" checked={state.form.state == 'effective'} onchange={actions.onChangeValue} /><span>{_T('Effective')}</span></label>
                      <label><input type="radio" name="state" value="disabled" checked={state.form.state == 'disabled'} onchange={actions.onChangeValue} /><span>{_T("Disabled")}</span></label>
                      <label><input type="radio" name="state" value="hidden" checked={state.form.state == 'hidden'} onchange={actions.onChangeValue} /><span>{_T("Hidden")}</span></label>
                    </fieldset>
                  </Control>
                  <Control label={_T('Note')} message={_T(state.messages['.note'])} note={_T('You can write in HTML.')}>
                    <textarea name="note" class="large-text" rows="3" value={state.form.note} oninput={actions.onInput} onblur={actions.onProductBlur}></textarea>
                  </Control>
                  <Control label={_T('Price')} message={_T(state.messages['.price'])}>
                    <input type="text" name="price" class="medium-text" value={state.form.price} oninput={actions.onInput} onblur={actions.onProductBlur} />
                  </Control>
                  <Control label={_T('Tax Rate')} message={_T(state.messages['.taxRate'])} note={_T('The tax rate on common settings will be applied when you leave it blank.')}>
                    <input type="text" name="taxRate" class="small-text" value={state.form.taxRate} oninput={actions.onInput} onblur={actions.onProductBlur} /> {_T('%')}
                  </Control>
                  <Control label={_T('Ribbons')} message={_T(state.messages['.doConfirm'])}>
                    <fieldset>
                      {['SALE', 'RECOMMENDED'].map((r) => (
                        <label><input type="checkbox" name={r} value={true} checked={state.form.ribbons.hasOwnProperty(r)} onchange={actions.onRibbonChange} /><span>{_T(r)}</span></label>
                      ))}
                    </fieldset>
                  </Control>
                </tbody>
              </table>
              <p class="submit">
                <button type="button" class="button button-primary" onclick={actions.onProductSettle}>{_T('Commit Changes')}</button>
                <button type="button" class="button" onclick={actions.onDismiss}>{_T('Discard Changes')}</button>
              </p>
            </form>
          ) : null}
        </div>
      </div>
    )
  }
}

// Attributes tab
const attributes = {
  createInitialState: (form) => {
    return {
      items: form.attrItems, 
      form: null, 
      messages: {}, 
      sorting: null, 
      nextItemId: Math.max(0, ...form.attrItems.map(x => x.id)) + 1, 
      spShowNav: true, 
      popping: false
    }
  }, 
  actions: {
    onShowNav: (ev) => (state, actions) => {
      return {...state, spShowNav:true}
    }, 
    onHideNav: (ev) => (state, actions) => {
      return {...state, spShowNav:false}
    }, 
    onDuplicate: (ev) => (state, actions) => {
      const idx = findIndexByProp("id", state.form.id, state.items)
      const item = deepCopy(state.items[idx])
      let nextItemId = state.nextItemId
      item.id = nextItemId++
      item.name = sprintf(_T('%s\'s Copy'), item.name)
      return {...state, nextItemId, items:linsert(state.items, idx + 1, item), form:item, messages:{}}
    }, 
    onDelete: (ev) => (state, actions) => {
      const idx = findIndexByProp("id", state.form.id, state.items)
      return {...state, items:lremove(state.items, idx), form:null, messages:{}}
    }, 
    onAdd: (ev) => (state, actions) => {
      const type = ev.currentTarget.value
      const idx = (state.form) ? findIndexByProp('id', state.form.id, state.items) + 1 : 0
      let nextItemId = state.nextItemId
      const item = {id:nextItemId++, type, required:true, name:_T('New Input Field'), note:""}
      if (type == "Name" || type == "Tel") {
        item.divided = false
      }
      if (type == "Name") {
        item.pattern = 'none'
      }
      if (type == "Email") {
        item.repeated = true
      }
      if (type == 'Address') {
        item.autoFill = 'none'
      }
      if (type == "Radio" || type == "Dropdown" || type == "MultiCheckbox") {
        item.options = _T('New Option')
      }
      if (type == "Radio" || type == "Checkbox" || type == 'Dropdown' || type == 'MultiCheckbox') {
        item.initialValue = "";
      }
      if (type == "Text") {
        item.multiline = false
        item.size = "normal"
        item.placeholder = ''
      }
      if (type == "reCAPTCHA3") {
        delete item.required
        delete item.name
        delete item.note
        item.siteKey = ""
        item.secretKey = ""
        item.action = "order"
        item.threshold1 = 0.6
        item.threshold2 = 0.2
      }
      return {...state, items:linsert(state.items, idx, item), nextItemId, form:item, messages:{}}
    }, 
    onSortStart: ({id:_id, index}) => (state, actions) => {
      return {...state, sorting:"yes"}
    }, 
    onSortEnd: ({fromId, fromIndex, toId, toIndex}) => (state, actions) => {
      return {...state, sorting:null, items:lmove(state.items, fromIndex, toIndex)}
    }, 
    onSelect: (ev) => (state, actions) => {
      const idx = ev.currentTarget.parentNode.id.split('-')[1]
      const item = state.items[idx]
      return {...state, form:item, messages:{}}
    }, 
    onImageSelect: ({name, image}) => ({form, ...rest}, actions) => {
      return {...rest, form:{...form, [name]:image ? image.url : null}}
    }, 
    onInput: (ev) => ({form, ...rest}, actions) => {
      return {...rest, form:{...form, [ev.currentTarget.name]:ev.currentTarget.value}}
    }, 
    onBlur: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      const path = '.'+ev.currentTarget.name
      const v = validate[rest.form.type]
      v(copy)
      messages = updateMessages(messages, path, v.errors)
      return {messages, ...rest}
    }, 
    onChange: (ev) => ({form, ...rest}, actions) => {
      const value = ev.currentTarget.value == "true"
      return {...rest, form:{...form, [ev.currentTarget.name]:value}}
    }, 
    onChangeEnum: (ev) => ({form:form0, ...rest}, actions) => {
      const name = ev.currentTarget.name
      const value = ev.currentTarget.value
      const form = {...form0, [ev.currentTarget.name]:value}
      return {...rest, form}
    }, 
    onSettle: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      const v = validate[rest.form.type]
      if (! v(copy)) {
        messages = createMessages(v.errors)
        focusErrorInput(messages)
        return {...rest, messages}
      }
      const idx = findIndexByProp("id", rest.form.id, rest.items)
      const items = lreplace(rest.items, idx, copy)
      return {...rest, messages:{}, form:null, items, spShowNav:true}
    }, 
    onDismiss: (ev) => (state, actions) => {
      return {...state, messages:{}, form:null, spShowNav:true}
    }, 
    onOpenExtra: (ev) => (state, actions) => {
      return {...state, popping:true}
    }, 
    onCloseExtra: (ev) => (state, actions) => {
      return {...state, popping:false}
    }
  }, 
  nameMap: {
    Name: 'Name', 
    Email: 'Mail Address', 
    Tel: 'Phone Number', 
    Address: 'Address', 
    Checkbox: 'Checkbox', 
    Radio: 'Radio Button', 
    Dropdown: 'Dropdown', 
    MultiCheckbox: 'Multiple Checkbox', 
    Text: 'Text', 
    reCAPTCHA3: 'reCAPTCHA v3'
  }, 
  iconMap: {
    Name: 'person', 
    Email: 'mail', 
    Tel: 'phone', 
    Address: 'my_location', 
    Checkbox: 'check_box', 
    Radio: 'radio_button_checked', 
    Dropdown: 'arrow_drop_down_circle', 
    MultiCheckbox: 'done_all', 
    Text: 'text_fields', 
    reCAPTCHA3: 'security'
  }, 
  view: (state, actions) => {
    const ns = attributes.nameMap
    return (
      <div class="wq-Details" key="details">
        <div class={`wq--nav ${state.spShowNav ? 'wq-is-spshown' : ''}`}>
          <div class="wq--tools">
            <IconButton icon={state.popping ? "expand_less" : "expand_more"} title={_T('Additional Menu')} onclick={state.popping ? actions.onCloseExtra : actions.onOpenExtra} />
            <IconButton icon="file_copy" title={_T('Duplicate')} onclick={actions.onDuplicate} disabled={!state.form} />
            <IconButton icon="delete" title={_T('Delete')} onclick={actions.onDelete} disabled={!state.form} />
            <div class="wq--spacer"></div>
            <IconButton icon="arrow_forward" title={_T('Close Nav')} onclick={actions.onHideNav} xclass="wq-for-close-nav" appearance={state.form ? 'primary' : ''} />
          </div>
          <div class={`wq-Menu ${state.popping ? 'wq-is-open' : ''}`}>
            <button class="wq-MenuItem" onclick={actions.onAdd} value="Name"><i class="material-icons">person</i> {_T(ns.Name)}</button>
            <button class="wq-MenuItem" onclick={actions.onAdd} value="Email"><i class="material-icons">mail</i> {_T(ns.Email)}</button>
            <button class="wq-MenuItem" onclick={actions.onAdd} value="Tel"><i class="material-icons">phone</i> {_T(ns.Tel)}</button>
            <button class="wq-MenuItem" onclick={actions.onAdd} value="Address"><i class="material-icons">my_location</i> {_T(ns.Address)}</button>
            <button class="wq-MenuItem" onclick={actions.onAdd} value="Checkbox"><i class="material-icons">check_box</i> {_T(ns.Checkbox)}</button>
            <button class="wq-MenuItem" onclick={actions.onAdd} value="Radio"><i class="material-icons">radio_button_checked</i> {_T(ns.Radio)}</button>
            <button class="wq-MenuItem" onclick={actions.onAdd} value="Dropdown"><i class="material-icons">arrow_drop_down_circle</i> {_T(ns.Dropdown)}</button>
            <button class="wq-MenuItem" onclick={actions.onAdd} value="MultiCheckbox"><i class="material-icons">done_all</i> {_T(ns.MultiCheckbox)}</button>
            <button class="wq-MenuItem" onclick={actions.onAdd} value="Text"><i class="material-icons">text_fields</i> {_T(ns.Text)}</button>
            <button class="wq-MenuItem" onclick={actions.onAdd} value="reCAPTCHA3"><i class="material-icons">security</i> {_T(ns.reCAPTCHA3)}</button>
          </div>
          <SortableList id="attributes" handle=".wq--handle-attributes" group="attributes" onSortStart={actions.onSortStart} onSortEnd={actions.onSortEnd} isRoot>
            {state.items.map((item, i0) => {
              return (
                <div class={`wq-ListItem wq-for-${item.type} ${state.form && state.form.id == item.id ? 'wq-is-selected' : ''}`} id={`attrItem-${i0}`}>
                  <div class="wq--main" onclick={actions.onSelect}>
                    <i class="material-icons">{attributes.iconMap[item.type]}</i> {item.type == "reCAPTCHA3" ? ns[item.type] : item.name}
                  </div>
                  <div class="wq--handle wq--handle-attributes"><i class="material-icons">drag_handle</i></div>
                </div>
              )
            })}
          </SortableList>
        </div>
        <div class={`wq--form`}>
          <div class="wq--lobe">
            <IconButton icon="arrow_back" onclick={actions.onShowNav} title={_T('Open Nav')} />
          </div>
          {state.form ? (
            <form novalidate key={`form-${state.form.id}`}>
              <table class="form-table wq-x-narrow">
                <tbody>
                  <Control label={_T('Type')}>
                    <p>{_T(ns[state.form.type])}</p>
                  </Control>
                  {state.form.type != "reCAPTCHA3" ? (
                    <Control label={_T('Name')} message={_T(state.messages['.name'])}>
                      <input type="text" name="name" class="regular-text" value={state.form.name} oninput={actions.onInput} onblur={actions.onBlur} />
                    </Control>
                  ) : null}
                  {state.form.type != "reCAPTCHA3" ? (
                    <Control label={_T('Input Required')} message={_T(state.messages['.required'])}>
                      <fieldset>
                        <label><input type="radio" name="required" value="true" checked={state.form.required} onchange={actions.onChange} /><span>{_T('Required')}</span></label>
                        <label><input type="radio" name="required" value="false" checked={!state.form.required} onchange={actions.onChange} /><span>{_T('Optional')}</span></label>
                      </fieldset>
                    </Control>
                  ) : null}
                  {state.form.type == "Name" || state.form.type == "Tel" ? (
                    <Control label={_T('Split Input Field')} message={_T(state.messages['.divided'])}>
                      <fieldset>
                        <label><input type="radio" name="divided" value="true" checked={state.form.divided} onchange={actions.onChange} /><span>{_T('Split')}</span></label>
                        <label><input type="radio" name="divided" value="false" checked={!state.form.divided} onchange={actions.onChange} /><span>{_T('Don\'t Split')}</span></label>
                      </fieldset>
                    </Control>
                  ): null}
                  {state.form.type == "Name" ? (
                    <Control label={_T('Input Restriction')} message={_T(state.messages['.pattern'])}>
                      <fieldset>
                        <label><input type="radio" name="pattern" value="none" checked={state.form.pattern == "none"} onchange={actions.onChangeEnum} /><span>{_T('None')}</span></label>
                        <label><input type="radio" name="pattern" value="hiragana" checked={state.form.pattern == "hiragana"} onchange={actions.onChangeEnum} /><span>{_T('Japanese Hiragana')}</span></label>
                        <label><input type="radio" name="pattern" value="katakana" checked={state.form.pattern == "katakana"} onchange={actions.onChangeEnum} /><span>{_T('Japanese Katakana')}</span></label>
                      </fieldset>
                    </Control>
                  ) : null}
                  {state.form.type == "Email" ? (
                    <Control label={_T('Confirmation Input')} message={_T(state.messages['.repeated'])} note={_T('Whether to have email address entered twice for confirmation.')}>
                      <fieldset>
                        <label><input type="radio" name="repeated" value={true} checked={state.form.repeated} onchange={actions.onChange} /><span>{_T('Confirm')}</span></label>
                        <label><input type="radio" name="repeated" value={false} checked={!state.form.repeated} onchange={actions.onChange} /><span>{_T('Don\'t Confirm')}</span></label>
                      </fieldset>
                    </Control>
                  ) : null}
                  {state.form.type == "Address" ? (
                    <Control label={_T('Auto Completion')} message={_T(state.messages['.autoFill'])} note={_T('Choose a service to auto-complete address from zip code.')}>
                      <fieldset>
                        <label><input type="radio" name="autoFill" value="none" checked={state.form.autoFill == "none"} onchange={actions.onChangeEnum} /><span>{_T('None')}</span></label>
                        <label><input type="radio" name="autoFill" value="yubinbango" checked={state.form.autoFill == 'yubinbango'} onchange={actions.onChangeEnum} /><span>{_T('Yubinbango (Japan)')}</span></label>
                      </fieldset>
                    </Control>
                  ) : null}
                  {state.form.type == "Radio" || state.form.type == "Dropdown" || state.form.type == "MultiCheckbox" ? (
                    <Control label={_T('Options')} message={_T(state.messages['.options'])} note={_T('Separate them with ",".')}>
                      <input type="text" name="options" class="large-text" value={state.form.options} oninput={actions.onInput} onblur={actions.onBlur} />
                    </Control>
                  ) : null}
                  {state.form.type == "Radio" || state.form.type == "Dropdown" || state.form.type == "MultiCheckbox" ? (
                    <Control label={_T('Initial Value')} message={_T(state.messages['.initialValue'])} note={state.form.type == "MultiCheckbox" ? _T('Separate them with ",".') : ''}>
                      <input type="text" name="initialValue" class="regular-text" value={state.form.initialValue} oninput={actions.onInput} onblur={actions.onBlur} />
                    </Control>
                  ) : null}
                  {state.form.type == "Checkbox" ? (
                    <Control label={_T('Initial Value')} message={_T(state.messages['.initialValue'])}>
                      <fieldset>
                        <label><input type="radio" name="initialValue" value="" checked={state.form.initialValue == ""} onchange={actions.onChangeEnum} /><span>{_T('Off')}</span></label>
                        <label><input type="radio" name="initialValue" value="1" checked={state.form.initialValue != ""} onchange={actions.onChangeEnum} /><span>{_T('On')}</span></label>
                      </fieldset>
                    </Control>
                  ) : null}
                  {state.form.type == "Text" ? (
                    <Control label={_T('Number of Lines')} message={_T(state.messages['.multiline'])}>
                      <fieldset>
                        <label><input type="radio" name="multiline" value="true" checked={state.form.multiline} onchange={actions.onChange} /><span>{_T('Multiple Lines')}</span></label>
                        <label><input type="radio" name="multiline" value="false" checked={!state.form.multiline} onchange={actions.onChange} /><span>{_T('1 Line')}</span></label>
                      </fieldset>
                    </Control>
                  ) : null}
                  {state.form.type == "Text" ? (
                    <Control label={_T('Width of Input Field')} message={_T(state.messages['.size'])}>
                      <fieldset>
                        <label><input type="radio" name="size" value="nano" checked={state.form.size == "nano"} onchange={actions.onChangeEnum} /><span>{_T('Nano')}</span></label> {_T('Up to 3 characters.')}<br />
                        <label><input type="radio" name="size" value="mini" checked={state.form.size == "mini"} onchange={actions.onChangeEnum} /><span>{_T('Mini')}</span></label> {_T('Up to 5 characters.')}<br />
                        <label><input type="radio" name="size" value="small" checked={state.form.size == "small"} onchange={actions.onChangeEnum} /><span>{_T('Small')}</span></label> {_T('Up to 8 characters.')}<br />
                        <label><input type="radio" name="size" value="normal" checked={state.form.size == "normal"} onchange={actions.onChangeEnum} /><span>{_T('Regular')}</span></label> {_T('Up to 13 characters.')}<br />
                        <label><input type="radio" name="size" value="full" checked={state.form.size == "full"} onchange={actions.onChangeEnum} /><span>{_T('Full')}</span></label> {_T('Full width')}
                      </fieldset>
                    </Control>
                  ) : null}
                  {state.form.type == "Text" ? (
                    <Control label={_T('Placeholder')} message={_T(state.messages['.placeholder'])}>
                      <input type="text" name="placeholder" class="regular-text" value={state.form.placeholder} oninput={actions.onInput} onblur={actions.onBlur} />
                    </Control>
                  ) : null}
                  {state.form.type != "reCAPTCHA3" ? (
                    <Control label={_T('Note')} message={_T(state.messages['.note'])} note={_T('You can write in HTML.')}>
                      <textarea name="note" class="large-text" rows="3" value={state.form.note} oninput={actions.onInput} onblur={actions.onBlur}></textarea>
                    </Control>
                  ) : null}
                  {state.form.type == "reCAPTCHA3" ? [(
                    <Control label={_T('Site Key')} message={_T(state.messages['.siteKey'])}>
                      <input type="text" name="siteKey" class="large-text" value={state.form.siteKey} oninput={actions.onInput} onblur={actions.onBlur} />
                    </Control>
                  ), (
                    <Control label={_T('Secret Key')} message={_T(state.messages['.secretKey'])}>
                      <input type="text" name="secretKey" class="large-text" value={state.form.secretKey} oninput={actions.onInput} onblur={actions.onBlur} />
                    </Control>
                  ), (
                    <Control label={_T('Action')} message={_T(state.messages['.action'])} note={[_T("A string that identifies the user's action. Refer: "), <a href="https://developers.google.com/recaptcha/docs/v3#actions" target="_blank">Actions</a>]}>
                      <input type="text" name="action" class="medium-text" value={state.form.action} oninput={actions.onInput} onblur={actions.onBlur} />
                    </Control>
                  ), (
                    <Control label={_T('Soft-Pass Score')} message={_T(state.messages['threshold1'])} note={_T("If the score is lower than this value, AForms considers that the submission is somewhat unreliable and email notifications to administrators will be omitted.")}>
                      <select name="threshold1" onchange={actions.onChangeEnum}>
                        {[0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1].map(s => {
                          return (
                            <option value={s} selected={s == state.form.threshold1}>{s}</option>
                          )
                        })}
                      </select>
                    </Control>
                  ), (
                    <Control label={_T('Failure Score')} message={_T(state.messages['threshold2'])} note={_T("If the score is lower than this value, AForms blocks the submission and show an error to customer.")}>
                      <select name="threshold2" onchange={actions.onChangeEnum}>
                        {[0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1].map(s => {
                          return (
                            <option value={s} selected={s == state.form.threshold2}>{s}</option>
                          )
                        })}
                      </select>
                    </Control>
                  )] : null}
                </tbody>
              </table>
              <p class="submit">
                <button type="button" class="button button-primary" onclick={actions.onSettle}>{_T('Commit Changes')}</button>
                <button type="button" class="button" onclick={actions.onDismiss}>{_T('Discard Changes')}</button>
              </p>
            </form>
          ) : null}
        </div>
      </div>
    )
  }
}

// Mail tab
const mail = {
  createInitialState: (form) => {
    return {
      mail: form.mail, 
      form: form.mail, 
      messages: {}
    }
  }, 
  actions: {
    onInput: (ev) => ({form, ...rest}, actions) => {
      return {...rest, form:{...form, [ev.currentTarget.name]:ev.currentTarget.value}}
    }, 
    onBlur: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      const path = '.'+ev.currentTarget.name
      const v = validate.Mail
      v(copy)
      messages = updateMessages(messages, path, v.errors)
      return {messages, ...rest}
    }, 
    onBoolChange: (ev) => ({form, ...rest}, actions) => {
      form = {...form, [ev.currentTarget.name]:ev.currentTarget.checked}
      return {...rest, form}
    }, 
    onSettle: (ev) => ({messages, ...rest}, actions) => {
      const copy = deepCopy(rest.form)
      const v = validate.Mail
      if (! v(copy)) {
        messages = createMessages(v.errors)
        focusErrorInput(v.errors)
        return {...rest, messages}
      }
      window.requestAnimationFrame(() => allActions.showNotice('Changes committed. Be sure to save data before moving to another page.'))
      return {...rest, messages:{}, mail:copy}
    }, 
    onDismiss: (ev) => (state, actions) => {
      return {...state, messages:{}, form:state.mail}
    }
  }, 
  view: (state, actions) => {
    const form = state.form
    return (
      <form novalidate id="form-mail" key="form-mail">
        <table class="form-table">
          <tbody>
            <Control label={_T('From Address')} message={_T(state.messages['.fromAddress'])}>
              <input type="text" class="large-text" name="fromAddress" value={form.fromAddress} oninput={actions.onInput} onblur={actions.onBlur} />
            </Control>
            <Control label={_T('From Name')} message={_T(state.messages['.fromName'])}>
              <input type="text" class="regular-text" name="fromName" value={form.fromName} oninput={actions.onInput} onblur={actions.onBlur} />
            </Control>
            <Control label={_T('Set Return-Path')} note={_T('Uncheck this if you prefer the default behavior of WordPress.')}>
              <fieldset>
                <label><input type="checkbox" name="alignReturnPath" value="1" checked={form.alignReturnPath} onchange={actions.onBoolChange} /> {_T('Set Return-Path to be the same as the From address')}</label>
              </fieldset>
            </Control>
            <Control label={_T('Subject')} message={_T(state.messages['.subject'])}>
              <input type="text" class="large-text" name="subject" value={form.subject} oninput={actions.onInput} onblur={actions.onBlur} />
            </Control>
            <Control label={_T('Notify To')} message={_T(state.messages['.notifyTo'])} note={_T('You can also send a copy of the thank-you-mail to another address. Separate them with "," to specify multiple addresses.')}>
              <input type="text" class="large-text" name="notifyTo" value={form.notifyTo} oninput={actions.onInput} onblur={actions.onBlur} />
            </Control>
            <Control label={_T('Text Body')} message={_T(state.messages['.textBody'])}>
              <p>{_T('You can insert the following data into the text body.')}</p>
              <dl class="wq--definitions">
                <dt>{'{{id}}'}</dt><dd>{_T('Order id')}</dd>
                <dt>{'{{details}}'}</dt><dd>{_T('Detail lines')}</dd>
                <dt>{'{{total}}'}</dt><dd>{_T('Total; In case of tax-excluded notation, subtotal and tax are included.')}</dd>
                <dt>{'{{attributes}}'}</dt><dd>{_T('Customer attributes')}</dd>
                <dt>{'{{name}}'}</dt><dd>{_T('Customer name; Available only when using Name control.')}</dd>
                <dt>{'{{email}}'}</dt><dd>{_T('Customer mail address; Available only when using MailAddress control.')}</dd>
              </dl>
              <textarea class="large-text" rows="20" name="textBody" value={form.textBody} oninput={actions.onInput} onblur={actions.onBlur}></textarea>
            </Control>
          </tbody>
        </table>
        <p class="submit">
          <button type="button" class="button button-primary" onclick={actions.onSettle}>{_T('Commit Changes')}</button>
          <button type="button" class="button" onclick={actions.onDismiss}>{_T('Discard Changes')}</button>
        </p>
      </form>
    )
  }
}

// whole page
const createInitialState = (form) => {
  const rv = {
    general: general.createInitialState(form), 
    details: details.createInitialState(form), 
    attributes: attributes.createInitialState(form), 
    mail: mail.createInitialState(form), 
    tab: 'general', 
    loading: false, 
    notification: null
  }
  return rv
}
const actions = {
  general: general.actions, 
  details: details.actions, 
  attributes: attributes.actions, 
  mail: mail.actions, 
  activate: (ev) => (state, actions) => {
    const off = ev.currentTarget.href.lastIndexOf('#')
    const tab = ev.currentTarget.href.slice(off + 1)
    return {...state, tab}
  }, 
  submit: (ev) => (state, actions) => {
    document.getElementById('save-button').setAttribute('disabled', 'disabled');
    const form = {
      id: state.general.formId, 
      title: state.general.general.title, 
      navigator: state.general.general.navigator, 
      doConfirm: state.general.general.doConfirm, 
      thanksUrl: state.general.general.thanksUrl, 
      detailItems: state.details.items, 
      attrItems: state.attributes.items, 
      mail: state.mail.mail
    }
    submit(form, actions.submitK)
    return {...state, loading:true}
  }, 
  submitK: (resp) => (state, actions) => {
    if (resp.form.id != state.general.formId) {
      history.replaceState('', '', editUrl.replace('placeholder', resp.form.id))
      const pu = pvUrl.replace('placeholder', resp.form.id)
      document.getElementById('preview-link').href = pu
    }
    window.requestAnimationFrame(() => allActions.general.updateId(resp.form.id))
    window.requestAnimationFrame(() => allActions.showNotice('Form saved.'))
    document.getElementById('save-button').removeAttribute('disabled');
    return {...state, loading:false}
  }, 
  showNotice: (notification) => (state, actions) => {
    scrollToTop();
    return {...state, notification}
  }, 
  hideNotice: (ev) => (state, actions) => {
    return {...state, notification:null}
  }
}
const bind = (_el) => {
  const target = document.getElementById('save-button');
  target.addEventListener('click', allActions.submit)
}
const view = (state, actions) => {
  return (
    <div oncreate={bind}>
      {(state.notification) ? (
        <div class="updated settings-error notice is-dismissible">
          <p><strong>{_T(state.notification)}</strong></p>
          <button type="button" class="notice-dismiss" onclick={actions.hideNotice}><span class="screen-reader-text">{_T('Dismiss this notice.')}</span></button>
        </div>
      ) : null}
      <nav class="nav-tab-wrapper wp-clearfix">
        <a href="#general" onclick={actions.activate} class={`nav-tab ${state.tab == 'general' ? 'nav-tab-active' : ''}`}>{_T('General')}</a>
        <a href="#details" onclick={actions.activate} class={`nav-tab ${state.tab == 'details' ? 'nav-tab-active' : ''}`}>{_T('Details')}</a>
        <a href="#attributes" onclick={actions.activate} class={`nav-tab ${state.tab == 'attributes' ? 'nav-tab-active' : ''}`}>{_T('Attributes')}</a>
        <a href="#mail" onclick={actions.activate} class={`nav-tab ${state.tab == 'mail' ? 'nav-tab-active' : ''}`}>{_T('Mail')}</a>
      </nav>
      {(state.tab == 'general') ? general.view(state.general, actions.general) : null}
      {(state.tab == 'details') ? details.view(state.details, actions.details) : null}
      {(state.tab == 'attributes') ? attributes.view(state.attributes, actions.attributes) : null}
      {(state.tab == 'mail') ? mail.view(state.mail, actions.mail) : null}
    </div>
  )
}

const _T = translate(wqData.catalog);
const noimageUrl = wqData.noimageUrl;
const submitUrl = wqData.submitUrl;
const editUrl = wqData.editUrl;
const pvUrl = wqData.pvUrl;
const allActions = app(createInitialState(wqData.form), actions, view, document.getElementById('root'))
const validate = mapHash((_name, s) => {
  return (new Ajv({coerceTypes: true, allErrors: true})).compile(s)
}, wqData.schemas);  // General, Auto, Selector, Option, ...

