
import { h, app } from 'hyperapp';
import { Core as YubinBangoCore } from 'yubinbango-core';
import number_format from 'locutus/php/strings/number_format';
import { tnOnCreate, tnOnRemove, Image, TextInput, TextArea, RadioButton, Checkbox, Select, Echo, Button, InputGroup, Control } from './front_component';


/*
 * Polyfills
 */
Math.trunc = Math.trunc || function(x) {
  return x < 0 ? Math.ceil(x) : Math.floor(x);
}

/*
 * ===============================================================
 * utilities
 */

function sprintf(format) {
  var args = arguments;
  var offset = 1;
  return format.replace(/%([0-9]\$)?([^0-9])/g, function (match, f1, f2) {
    if (f2 == '%') {
      return '%';
    } else if (f2 == 's') {
      if (! f1) {
        return args[offset++];
      } else {
        return args[f1.slice(0, 1)];
      }
    }
  });
}

const nf = (amount) => {
  return number_format(amount, rule.taxPrecision, catalog.decPoint, catalog.thousandsSep);
}

const br = {nodeName:'br', attributes:{}, children:[]}

const _Tv = (x) => {
  x = _T(x)
  if (typeof x != 'string') return x

  const lines = x.split('\n')
  if (lines.length == 1) return lines[0]
  
  return lines.reduce((cur, line) => {
    return (cur.length == 0) ? [line] : [...cur, br, line]
  }, [])
}

const _T = (x) => {
  if (typeof x == 'undefined' || x === null) return null
  if (catalog.hasOwnProperty(x)) {
    return catalog[x]
  } else {
    console.log('TO TRANSLATE: ', x);
    return x;
  }
}

const scrollTo = (id, just = false) => {
  const pos = document.documentElement.scrollTop || document.body.scrollTop;
  const r = document.getElementById(id).getBoundingClientRect()
  const posBot = r.top + pos - (just ? 0 : 100)
  const posTop = r.top + pos - (just ? 1 : 150)
  if (posTop > pos) {
    const diff = Math.max((posTop - pos) / 8, 4)
    if (behavior.smoothScroll && posTop - pos > 4) {
      window.requestAnimationFrame(() => scrollTo(id, just))
      window.scrollTo(0, pos + diff)
    } else {
      window.scrollTo(0, posTop)
    }
  } else if (posBot < pos) {
    const diff = Math.max((pos - posBot) / 8, 4)
    if (behavior.smoothScroll && pos - posBot > 4) {
      window.requestAnimationFrame(() => scrollTo(id, just))
      window.scrollTo(0, pos - diff)
    } else {
      window.scrollTo(0, posBot)
    }
  }
}

const focusErrorInput = (prefix, ms) => {
  const ids = Object.keys(ms)
  if (ids.length == 0) return;
  const name = '.'+prefix+ids[0]
  const elem = document.querySelector(name);
  if (elem != null) {
    elem.focus();
    return;
  }
}

const findByProp = (name, val, arr) => {
  const len = arr.length
  for (let i = 0; i < len; i++) {
    if (arr[i][name] == val) return arr[i]
  }
  return undefined
}

const subsetOf = (set, target) => {
  for (let prop in target) {
    if (! set.hasOwnProperty(prop)) return false;
  }
  return true;
}

//const exclude = (set, prop) => {
//  const copy = {...set}
//  delete copy[prop]
//  return copy
//}

const branchNo = (name, sep) => {
  const off = name.lastIndexOf(sep)
  const fragment = name.slice(off + 1)
  return parseInt(fragment)
}

const replaceElement = (arr, idx, value) => {
  const arr2 = [...arr]
  arr2[idx] = value
  return arr2
}

const indexOf = (e, lis) => {
  const len = lis.length
  for (let i = 0; i < len; i++) {
    if (lis[i] == e) return i
  }
  return -1
}

const reduceHash = (f, cur, hash) => {
  for (let key in hash) {
    cur = f(cur, key, hash[key])
  }
  return cur
}

const lreplace = (arr, idx, e) => {
  return arr.map((e0, i) => (i == idx) ? e : e0)
}

/*const mapHash = (f, hash) => {
  const rv = {}
  for (let key in hash) {
    rv[key] = f(key, hash[key])
  }
  return rv
}*/

const emptyString = (s) => (s == "")

const normalizePrice = (rule, price) => {
  price = price * Math.pow(10, rule.taxPrecision)
  switch (rule.taxNormalizer) {
    case 'trunc': price = Math.trunc(price); break;
    case 'floor': price = Math.floor(price); break;
    case 'ceil':  price = Math.ceil(price); break;
    case 'round': price = Math.round(price); break;
  }
  return price / Math.pow(10, rule.taxPrecision)
}

const compare = (value, equation, threshold) => {
  switch (equation) {
    case 'equal': return value == threshold
    case 'notEqual': return value != threshold
    case 'greaterThan': return value > threshold
    case 'greaterEqual': return value >= threshold
    case 'lessThan': return value < threshold
    case 'lessEqual': return value <= threshold
  }
}

const compare2 = (value, lower, lowerIncluded, higher, higherIncluded) => {
  if (lower != null) {
    if (lowerIncluded) {
      if (value < lower) return false;
    } else {
      if (value <= lower) return false;
    }
  }
  if (higher != null) {
    if (higherIncluded) {
      if (value > higher) return false;
    } else {
      if (value >= higher) return false;
    }
  }
  return true;
}

const submit = (data, k, submitType = 'submit') => {
  //console.log('submit', submitType);
  jQuery.ajax({
    type: "post", 
    url: (submitType == 'submit') ? submitUrl : confirmUrl, 
    data: JSON.stringify(data), 
    contentType: 'application/json',
    success: function(response) {
      k([response, submitType])
    }, 
    error: function (xhr) {
      const msg = (xhr.status === 403) ? _T('Failed to submit due to authorization failure.') : _T('Failed to submit due to some error.')
      window.alert(msg)
    }, 
    dataType: 'json'
  });
}

const complementAddress = (zip, k) => {
  new YubinBangoCore(zip.replace('-', ''), k)
}

/*
 * ===============================================================
 * view 
 */

const Product = (
    {
      quantity, 
      groupid, 
      product, 
      onincr, 
      ondecr, 
      navigator
    }) => {
  if (product.state == 'hidden') return null
  const id = `wqe-product-detail-${groupid}-${product.id}`
  const iname = `detail-${groupid}-${product.id}`
  const price = pricePrefix + nf(product.price) + priceSuffix
  return (
    <div class={`wqe-Product wqe-belongs-${navigator} wqe-belongs-group-${groupid} ${quantity > 0 ? 'wqe-is-selected' : ''} ${product.state == 'disabled' ? 'wqe-is-disabled': ''}`} key={id} id={id+'-wrapper'}>
      <div class="wqe--info">
        <div class="wqe--media">
          <Image src={product.image || wqData.noimageurl} scaling="user-defined" xclass={`wqe-belongs-${navigator} wqe-belongs-product`} />
        </div>
        <div class="wqe--main">
          <div class="wqe--name">{product.name}</div>
          <div class="wqe--note">{product.note}</div>
          <div class="wqe--prices">
            <span class="wqe--price">{price}</span>
          </div>
        </div>
        {['SALE', 'RECOMMENDED'].map((r,i) => product.ribbons[r] ? (
          <div class={`wqe--ribbon wqe-ribbon-${i+1}`}><span>{_Tv(r)}</span></div>
        ) : null)}
        {product.state == 'disabled' ? (
          <div class={`wqe--ribbon wqe-ribbon-disabled`}><span>{_Tv('Sold Out')}</span></div>
        ) : null}
      </div>
      <div class="wqe--control">
        <Button type="normal" onclick={ondecr} name={iname} xclass="wqe-for-decr wqe-belongs-product" disabled={product.state == 'disabled'}>{_Tv('-')}</Button>
        <div class="wqe--quantity">{quantity}</div>
        <Button type="normal" onclick={onincr} name={iname} xclass="wqe-for-incr wqe-belongs-product" disabled={product.state == 'disabled'}>{_Tv('+')}</Button>
      </div>
    </div>
  )
}

const Group = (
    {
      group, 
      selectedProducts, 
      onproductdecr, 
      onproductincr, 
      navigator
    }
) => {
  if (! group.visible || group.products.every(p => p.state == "hidden")) return null
  const id = `wqe-group-detail-${group.id}`
  return (
    <div class={`wqe-Group wqe-lct-enabled wqe-belongs-${navigator}`} key={id} id={id} oncreate={tnOnCreate} onremove={tnOnRemove}>
      <div class="wqe--head">
        <div class="wqe--media">
          <Image src={group.image} scaling="center" xclass={`wqe-belongs-${navigator} wqe-belongs-selector`} />
        </div>
        <div class="wqe--main">
          <div class="wqe--name">{group.name}</div>
        </div>
      </div>
      <div class="wqe--body">
        <div class="wqe--note">{group.note}</div>
        {group.products.map((product, i) => {
          const quantity = selectedProducts.hasOwnProperty(product.id) ? selectedProducts[product.id] : 0
          return (
            <Product quantity={quantity} groupid={group.id} product={product} onincr={onproductincr} ondecr={onproductdecr} navigator={navigator} />
          )
        })}
      </div>
    </div>
  )
}

const HNavigator = (
    {
      data, 
      current, 
      onproductdecr, 
      onproductincr, 
    }, children) => {
  const id = 'wqe-hnavigator'
  return (
    <div class="wqe-HNavigator" id={id} key={id}>
      {children}
      <div class="wqe--items">
        {form.detailItems.map((item) => {
          if (item.type == 'Group' && item.id == current) {
            const selectedProducts = data.hasOwnProperty(item.id) ? data[item.id] : {}
            return (<Group group={item} selectedProducts={selectedProducts} onproductdecr={onproductdecr} onproductincr={onproductincr} navigator="hnavigator" />)
          } else {
            return null;
          }
        })}
      </div>
    </div>
  )
}

const suspendOtherSurfaces = () => {
  document.documentElement.classList.add('wqe-x-suspended')
}
const resumeOtherSurfaces = () => {
  document.documentElement.classList.remove('wqe-x-suspended')
}
const overlayOnCreate = (el) => {
  suspendOtherSurfaces()
  tnOnCreate(el)
}
const overlayOnRemove = (el, done) => {
  const myDone = () => {
    resumeOtherSurfaces()
    done()
  }
  tnOnRemove(el, myDone)
}


const Monitor = (
    {
      detailsState, 
      confirming, 
      monitorPos, 
      spShown, 
      ondecr, 
      onincr, 
      onHide
    }) => {
  const id = `wqe-monitor`
  return (
    <div class={`wqe-Monitor ${confirming ? 'wqe-is-confirming' : ''} ${spShown ? 'wqe-is-spshown' : ''} wqe-sticks-${monitorPos.v}`} id={id} key={id} style={{left:monitorPos.left+"px"}}>
      <div class="wqe--header">
        <div class="wqe--title">{_Tv('Shopping Cart')}</div>
        <div class="wqe--menu">
          <Button type="normal" onclick={onHide} xclass="wqe-belongs-monmenu wqe-for-hidemonitor"><span class="dashicons dashicons-no-alt"></span></Button>
        </div>
      </div>
      <div class="wqe--entries">
        <div class="wqe--entry wqe-for-header">
          <div class="wqe--prop wqe-for-no">{_Tv('No')}</div>
          <div class="wqe--prop wqe-for-entry">{_Tv('Entry')}</div>
          <div class="wqe--prop wqe-for-unitPrice">{_Tv('Unit Price')}</div>
          <div class="wqe--prop wqe-for-quantity">{_Tv('Quantity')}</div>
          <div class="wqe--prop wqe-for-price">{_Tv('Price')}</div>
          {!rule.taxIncluded && (<div class="wqe--prop wqe-for-taxClass">{_Tv('Tax Class')}</div>)}
          <div class="wqe--prop wqe-for-operation">{_Tv('Operation')}</div>
        </div>
        {detailsState.details.map((detail, i) => {
          const id = `wqe-monitor-entry-${detail.key}`
          return (
            <div class="wqe--entry wqe-for-entry wqe-lct-enabled" key={id} id={id} oncreate={tnOnCreate} onremove={tnOnRemove}>
              <div class="wqe--prop wqe-for-no">{i + 1}</div>
              <div class="wqe--prop wqe-for-entry">{detail.name}</div>
              <div class="wqe--prop wqe-for-unitPrice">{nf(detail.unitPrice)}</div>
              <div class="wqe--prop wqe-for-quantity">{detail.quantity}</div>
              <div class="wqe--prop wqe-for-price">{nf(normalizePrice(rule, detail.unitPrice * detail.quantity))}</div>
              {!rule.taxIncluded && (<div class="wqe--prop wqe-for-taxClass">{detail.taxRate === null ? sprintf(_Tv('(common %s%% applied)'), ""+rule.taxRate) : sprintf(_Tv('(%s%% applied)'), detail.taxRate)}</div>)}
              {detail.operatable ? (
                <div class="wqe--prop wqe-for-operation">
                  <Button type="normal" name={`decr-${detail.key}`} onclick={ondecr} xclass="wqe-belongs-monitor wqe-for-decr">{_Tv('-')}</Button>
                  <Button type="normal" name={`incr-${detail.key}`} onclick={onincr} xclass="wqe-belongs-monitor wqe-for-incr">{_Tv('+')}</Button>
                </div>
              ) : (
                <div class="wqe--prop wqe-for-operation wqe-is-empty"></div>
              )}
            </div>
          )
        })}
      </div>
      {rule.taxIncluded 
        ? (() => {
          return (
            <div class="wqe--footer">
              <div class="wqe--entry wqe-for-total">
                <div class="wqe--prop wqe-for-name">{_Tv('Total')}</div>
                <div class="wqe--prop wqe-for-value">{pricePrefix}<span><span class="wqe-lct-enabled" id={detailsState.total} key={detailsState.total} oncreate={tnOnCreate} onremove={tnOnRemove}>{nf(detailsState.total)}</span></span>{priceSuffix}</div>
              </div>
            </div>
          )
        })() : (() => {
          const subtotal = detailsState.total
          const totalwt = reduceHash((cur, key, tax) => {
            return cur + tax
          }, subtotal, detailsState.taxes)
          const defaultTax = detailsState.taxes.hasOwnProperty('') ? detailsState.taxes[''] : null
          return (
            <div class="wqe--footer">
              <div class="wqe--entry wqe-for-subtotal">
                <div class="wqe--prop wqe-for-name">{_Tv('Subtotal')}</div>
                <div class="wqe--prop wqe-for-value">{pricePrefix}<span><span class="wqe-lct-enabled" id={subtotal} key={subtotal} oncreate={tnOnCreate} onremove={tnOnRemove}>{nf(subtotal)}</span></span>{priceSuffix}</div>
              </div>
              {defaultTax !== null ? (
                <div class="wqe--entry wqe-for-tax wqe-rate-common">
                  <div class="wqe--prop wqe-for-name">{sprintf(_Tv('Tax (common %s%%)'), ""+rule.taxRate)}</div>
                  <div class="wqe--prop wqe-for-value">{pricePrefix}<span><span class="wqe-lct-enabled" id={defaultTax} key={defaultTax} oncreate={tnOnCreate} onremove={tnOnRemove}>{nf(defaultTax)}</span></span>{priceSuffix}</div>
                </div>
              ) : null}
              {reduceHash((cur, key, tax) => {
                if (key === "") return cur
                return [...cur, 
                  <div class="wqe--entry wqe-for-tax wqe-rate-individual">
                    <div class="wqe--prop wqe-for-name">{sprintf(_Tv('Tax (%s%%)'), ""+key)}</div>
                    <div class="wqe--prop wqe-for-value">{pricePrefix}<span><span class="wqe-lct-enabled" id={tax} key={tax} oncreate={tnOnCreate} onremove={tnOnRemove}>{nf(tax)}</span></span>{priceSuffix}</div>
                  </div>
                ]
              }, [], detailsState.taxes)}
              <div class="wqe--entry wqe-for-total">
                <div class="wqe--prop wqe-for-name">{_Tv('Total')}</div>
                <div class="wqe--prop wqe-for-value">{pricePrefix}<span><span class="wqe-lct-enabled" id={totalwt} key={totalwt} oncreate={tnOnCreate} onremove={tnOnRemove}>{nf(totalwt)}</span></span>{priceSuffix}</div>
              </div>
            </div>
          )
        })()
      }
    </div>
  )
}

const attrItem_table = {}
const MSG_REQUIRED = 'Input here'
const MSG_INVALID = 'Invalid'
const MSG_TOCHECK = 'Check here'
const MSG_TOSELECT = 'Select here'
const MSG_DIFFERENT = 'Repeat here'
const MSG_TOOSMALL = 'Too small'
const MSG_TOOLARGE = 'Too large'
const MSG_HIRAGANA = 'Input in Hiragana'
const MSG_KATAKANA = 'Input in Katakana'

// {type, id, name, required, note, divided}
attrItem_table.Name = {}
attrItem_table.Name.view = (item, state, actions) => {
  const id = `wqe-attr-name-${item.id}`
  if (! item.divided) {
    const name = `name-${item.id}`
    return (
      <Control label={item.name} required={item.required} message={_Tv(state.message)} note={item.note} requiredText={_Tv(item.required ? 'required' : 'optional')} id={id} xclass={`wqe-belongs-attr wqe-for-name`} key={id}>
        <TextInput type="text" size="normal" name={name} placeholder={_T('Your Name')} value={state.value} invalid={!!state.message} oninput={actions.oninput} onblur={actions.onblur} xclass={`wqe-belongs-attr wqe-belongs-name wq_attr-${item.id}`} />
      </Control>
    )
  } else {
    const names = [`name-${item.id}-0`, `attr-${item.id}-1`]
    return (
      <Control label={item.name} required={item.required} message={_Tv(state.message)} note={item.note} requiredText={_Tv(item.required ? 'required' : 'optional')} id={id} xclass={`wqe-belongs-attr wqe-for-name wqe-is-divided`} key={id}>
        <InputGroup gutter="mini" xclass={`wqe-belongs-attr wqe-belongs-name`}>
          <TextInput type="text" size="small" name={names[0]} placeholder={_T('First Name')} value={state.value[0]} invalid={!!state.message} oninput={actions.oninput} onblur={actions.onblur} xclass={`wqe-belongs-attr wqe-belongs-name wq_attr-${item.id}`} />
          <TextInput type="text" size="small" name={names[1]} placeholder={_T('Last Name')} value={state.value[1]} invalid={!!state.message} oninput={actions.oninput} onblur={actions.onblur} xclass={`wqe-belongs-attr wqe-belongs-name wq_attr-${item.id}`} />
        </InputGroup>
      </Control>
    )
  }
}
attrItem_table.Name.initialState = (item) => {
  if (item.divided) {
    return {value:["", ""], message:null}
  } else {
    return {value:"", message:null}
  }
}
attrItem_table.Name.compile = (item, state) => {
  if (item.divided) {
    return state.value.join(' ')
  } else {
    return state.value
  }
}
attrItem_table.Name.hiraganaPattern = /^([ぁ-ん]|ー| |　)+$/
attrItem_table.Name.katakanaPattern = /^([ァ-ン]|ー| |　)+$/
attrItem_table.Name.validate = (item, state) => {
  if (item.divided) {
    if (!item.required && state.value[0] == "" && state.value[1] == "") {
      // thru
    } else if (state.value[0] == "" || state.value[1] == "") {
      return {value:state.value, message:MSG_REQUIRED}
    } else if (item.pattern == 'hiragana' && (!state.value[0].match(attrItem_table.Name.hiraganaPattern) || !state.value[1].match(attrItem_table.Name.hiraganaPattern))) {
      return {value:state.value, message:MSG_HIRAGANA}
    } else if (item.pattern == 'katakana' && (!state.value[0].match(attrItem_table.Name.katakanaPattern) || !state.value[1].match(attrItem_table.Name.katakanaPattern))) {
      return {value:state.value, message:MSG_KATAKANA}
    }
    return {value:state.value, message:null}
  } else {
    if (!item.required && state.value == "") {
      // thru
    } else if (state.value == "") {
      return {value:state.value, message:MSG_REQUIRED}
    } else if (item.pattern == 'hiragana' && !state.value.match(attrItem_table.Name.hiraganaPattern)) {
      return {value:state.value, message:MSG_HIRAGANA}
    } else if (item.pattern == 'katakana' && !state.value.match(attrItem_table.Name.katakanaPattern)) {
      return {value:state.value, message:MSG_KATAKANA}
    }
    return {value:state.value, message:null}
  }
}
attrItem_table.Name.createActions = (item) => {
  if (item.divided) {
    return {
      oninput: (ev) => (state, _actions) => {
        const idx = branchNo(ev.currentTarget.name, '-')
        return {...state, value:replaceElement(state.value, idx, ev.currentTarget.value)}
      }, 
      onblur: (ev) => (state, _actions) => {
        const idx = branchNo(ev.currentTarget.name, '-')
        if (idx == 1) {
          if (!item.required && state.value[0] == "" && state.value[1] == "") {
            // thru
          } else if (state.value[0] == "" || state.value[1] == "") {
            return {value:state.value, message:MSG_REQUIRED}
          } else if (item.pattern == 'hiragana' && (!state.value[0].match(attrItem_table.Name.hiraganaPattern) || !state.value[1].match(attrItem_table.Name.hiraganaPattern))) {
            return {value:state.value, message:MSG_HIRAGANA}
          } else if (item.pattern == 'katakana' && (!state.value[0].match(attrItem_table.Name.katakanaPattern) || !state.value[1].match(attrItem_table.Name.katakanaPattern))) {
            return {value:state.value, message:MSG_KATAKANA}
          }
        }
        return {...state, message:null}
      }
    }
  } else {
    return {
      oninput: (ev) => (state, _actions) => {
        return {value:ev.currentTarget.value, message:state.message}
      }, 
      onblur: (ev) => (state, _actions) => {
        if (!item.required && state.value == "") {
          // thru
        } else if (state.value == "") {
          return {value:state.value, message:MSG_REQUIRED}
        } else if (item.pattern == 'hiragana' && !state.value.match(attrItem_table.Name.hiraganaPattern)) {
          return {value:state.value, message:MSG_HIRAGANA}
        } else if (item.pattern == 'katakana' && !state.value.match(attrItem_table.Name.katakanaPattern)) {
          return {value:state.value, message:MSG_KATAKANA}
        }
        return {value:state.value, message:null}
      }
    }
  }
}

// {type, id, name, required, note, repeated}
attrItem_table.Email = {}
attrItem_table.Email.view = (item, state, actions) => {
  const id = `wqe-attr-email-${item.id}`
  if (item.repeated) {
    const names = [`email-${item.id}-0`, `email-${item.id}-1`]
    return (
      <Control label={item.name} required={item.required} message={_Tv(state.message)} note={item.note} requiredText={_Tv(item.required ? 'required' : 'optional')} id={id} xclass="wqe-belongs-attr wqe-for-email" key={id}>
        <TextInput type="text" size="full" name={names[0]} placeholder={_T('info@example.com')} value={state.value[0]} invalid={!!state.message} oninput={actions.oninput} onblur={actions.onblur} xclass={`wqe-belongs-attr wqe-belongs-email wq_attr-${item.id}`} />
        <TextInput type="text" size="full" name={names[1]} placeholder={_T('Confirm again')} value={state.value[1]} invalid={!!state.message} oninput={actions.oninput} onblur={actions.onblur} xclass={`wqe-belongs-attr wqe-belongs-email wq_attr-${item.id}`} />
      </Control>
    )
  } else {
    const name = `email-${item.id}`
    return (
      <Control label={item.name} required={item.required} message={_Tv(state.message)} note={item.note} requiredText={_Tv(item.required ? 'required' : 'optional')} id={id} xclass="wqe-belongs-attr wqe-for-email" key={id}>
        <TextInput type="text" size="full" name={name} placeholder={_T('info@example.com')} value={state.value} invalid={!!state.message} oninput={actions.oninput} onblur={actions.onblur} xclass={`wqe-belongs-attr wqe-belongs-email wq_attr-${item.id}`} />
      </Control>
    )
  }
}
attrItem_table.Email.pattern = /^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/
attrItem_table.Email.initialState = (item) => {
  if (item.repeated) {
    return {value:["", ""], message:null}
  } else {
    return {value:"", message:null}
  }
}
attrItem_table.Email.compile = (item, state) => {
  if (item.repeated) {
    return state.value[0]
  } else {
    return state.value
  }
}
attrItem_table.Email.validate = (item, state) => {
  if (item.repeated) {
    if (!item.required && state.value[0] == "" && state.value[1] == "") {
      // thru
    } else if (state.value[0] == "" || state.value[1] == "") {
      return {...state, message:MSG_REQUIRED}
    } else if (!state.value[0].match(attrItem_table.Email.pattern)) {
      return {...state, message:MSG_INVALID}
    } else if (state.value[0] != state.value[1]) {
      return {...state, message:MSG_DIFFERENT}
    }
    return {...state, message:null}
  } else {
    if (!item.required && state.value == "") {
      // thru
    } else if (state.value == "") {
      return {value:state.value, message:MSG_REQUIRED}
    } else if (!state.value.match(attrItem_table.Email.pattern)) {
      return {value:state.value, message:MSG_INVALID}
    }
    return {value:state.value, message:null}
  }
}
attrItem_table.Email.createActions = (item) => {
  if (item.repeated) {
    return {
      oninput: (ev) => (state, _actions) => {
        const idx = branchNo(ev.currentTarget.name, '-')
        return {...state, value:replaceElement(state.value, idx, ev.currentTarget.value)}
      }, 
      onblur: (ev) => ({value, _message}, _actions) => {
        const idx = branchNo(ev.currentTarget.name, '-')
        if (idx == 1) {
          if (!item.required && value[0] == "" && value[1] == "") {
            // thru
          } else if (value[0] == "" || value[1] == "") {
            return {value, message:MSG_REQUIRED}
          } else if (!value[0].match(attrItem_table.Email.pattern)) {
            return {value, message:MSG_INVALID}
          } else if (value[0] != value[1]) {
            return {value, message:MSG_DIFFERENT}
          }
        }
        return {value, message:null}
      }
    }
  } else {
    return {
      oninput: (ev) => (state, _actions) => {
        return {value:ev.currentTarget.value, message:state.message}
      }, 
      onblur: (ev) => ({value, _message}, _actions) => {
        if (!item.required && value == "") {
          // thru
        } else if (value == "") {
          return {value, message:MSG_REQUIRED}
        } else if (!value.match(attrItem_table.Email.pattern)) {
          return {value, message:MSG_INVALID}
        }
        return {value, message:null}
      }
    }
  }
}

// {type, id, name, divided, required, note}
attrItem_table.Tel = {}
attrItem_table.Tel.view = (item, state, actions) => {
  const id = `wqe-attr-tel-${item.id}`
  if (item.divided) {
    const name = `tel-${item.id}`
    const phs = _T('03-1111-2222').split('-')
    return (
      <Control label={item.name} required={item.required} message={_Tv(state.message)} note={item.note} requiredText={_Tv(item.required ? 'required' : 'optional')} id={id} xclass="wqe-belongs-attr wqe-for-tel" key={id}>
        <InputGroup xclass="wqe-belongs-attr wqe-belongs-tel">
          <TextInput type="tel" size="nano" name={name+'-0'} placeholder={phs[0]} value={state.value[0]} invalid={!!state.message} oninput={actions.oninput} onblur={actions.onblur} xclass={`wqe-belongs-attr wqe-belongs-tel wq_attr-${item.id}`} />
          <span>-</span>
          <TextInput type="tel" size="mini" name={name+'-1'} placeholder={phs[1]} value={state.value[1]} invalid={!!state.message} oninput={actions.oninput} onblur={actions.onblur} xclass={`wqe-belongs-attr wqe-belongs-tel wq_attr-${item.id}`} />
          <span>-</span>
          <TextInput type="tel" size="mini" name={name+'-2'} placeholder={phs[2]} value={state.value[2]} invalid={!!state.message} oninput={actions.oninput} onblur={actions.onblur} xclass={`wqe-belongs-attr wqe-belongs-tel wq_attr-${item.id}`} />
        </InputGroup>
      </Control>
    )
  } else {
    const name = `tel-${item.id}`
    return (
      <Control label={item.name} required={item.required} message={_Tv(state.message)} note={item.note} requiredText={_Tv(item.required ? 'required' : 'optional')} id={id} xclass="wqe-belongs-attr wqe-for-tel" key={id}>
        <TextInput type="tel" size="small" name={name} placeholder={_T('03-1111-2222')} value={state.value} invalid={!!state.message} oninput={actions.oninput} onblur={actions.onblur} xclass={`wqe-belongs-attr wqe-belongs-tel wq_attr-${item.id}`} />
      </Control>
    )
  }
}
attrItem_table.Tel.initialState = (item) => {
  if (item.divided) {
    return {value:["", "", ""], message:null}
  } else {
    return {value:"", message:null}
  }
}
attrItem_table.Tel.compile = (item, state) => {
  if (item.divided) {
    return (state.value[0] == '') ? '' : state.value.join('-')
  } else {
    return state.value
  }
}
attrItem_table.Tel.validate = (item, state) => {
  if (item.divided) {
    if (!item.required && state.value.every(emptyString)) {
      // thru
    } else if (state.value.some(emptyString)) {
      return {value:state.value, message:MSG_REQUIRED}
    } else if (! state.value.join('').match(/^[0-9]+$/)) {
      return {value:state.value, message:MSG_INVALID}
    }
    return {value:state.value, message:null}
  } else {
    if (!item.required && state.value == "") {
      // thru
    } else if (state.value == "") {
      return {value:state.value, message:MSG_REQUIRED}
    } else if (! state.value.match(/^[0-9-]+$/)) {
      return {value:state.value, message:MSG_INVALID}
    }
    return {value:state.value, message:null}
  }
}
attrItem_table.Tel.createActions = (item) => {
  if (item.divided) {
    return {
      oninput: (ev) => (state, _actions) => {
        const idx = branchNo(ev.currentTarget.name, '-')
        return {...state, value:replaceElement(state.value, idx, ev.currentTarget.value)}
      }, 
      onblur: (ev) => ({value, _message}, _actions) => {
        const idx = branchNo(ev.currentTarget.name, '-')
        if (idx == 2) {
          if (!item.required && value.every(emptyString)) {
            // thru
          } else if (value.some(emptyString)) {
            return {value, message:MSG_REQUIRED}
          } else if (! value.join('').match(/^[0-9]+$/)) {
            return {value, message:MSG_INVALID}
          }
        }
        return {value, message:null}
      }
    }
  } else {
    return {
      oninput: (ev) => (state, _actions) => {
        return {...state, value:ev.currentTarget.value}
      }, 
      onblur: (ev) => ({value, _message}, _actions) => {
        if (!item.required && value == "") {
          // thru
        } else if (value == "") {
          return {value, message:MSG_REQUIRED}
        } else if (! value.match(/^[0-9-]+$/)) {
          return {value, message:MSG_INVALID}
        }
        return {value, message:null}
      }
    }
  }
}

// {type, id, name, required, note, autoFill}
attrItem_table.Address = {}
attrItem_table.Address.view = (item, state, actions) => {
  const id = `wqe-attr-address-${item.id}`
  const name = `address-${item.id}`
  return (
    <Control label={item.name} required={item.required} message={_Tv(state.message)} note={item.note} requiredText={_Tv(item.required ? 'required' : 'optional')} id={id} xclass={`wqe-belongs-attr wqe-for-address`} key={id}>
      <InputGroup gutter="mini" xclass="wqe-belongs-attr wqe-belongs-address">
        <span>{_Tv('Zip')}</span>
        <TextInput type="tel" name={`${name}-0`} size="small" value={state.value[0]} oninput={actions.oninput} onblur={actions.onblur} placeholder={_T('000-0000')} invalid={!!state.message} xclass={`wqe-belongs-attr wqe-belongs-address wqe-for-zip wq_attr-${item.id}`} />
      </InputGroup>
      <InputGroup gutter="mini" xclass="wqe-belongs-attr wqe-belongs-address">
        <TextInput type="text" name={`${name}-1`} size="small" value={state.value[1]} oninput={actions.oninput} onblur={actions.onblur} placeholder={_T('Tokyo')} invalid={!!state.message} xclass={`wqe-belongs-attr wqe-belongs-address wqe-for-pref wq_attr-${item.id}`} />
        <TextInput type="text" name={`${name}-2`} size="small" value={state.value[2]} oninput={actions.oninput} onblur={actions.onblur} placeholder={_T('Chiyoda-ku')} invalid={!!state.message} xclass={`wqe-belongs-attr wqe-belongs-address wqe-for-city wq_attr-${item.id}`} />
      </InputGroup>
      <TextInput type="text" name={`${name}-3`} size="full" value={state.value[3]} oninput={actions.oninput} onblur={actions.onblur} placeholder={_T('1-1-1, Chiyoda')} invalid={!!state.message} xclass={`wqe-belongs-attr wqe-belongs-address wqe-for-street wq_attr-${item.id}`} />
      <TextInput type="text" name={`${name}-4`} size="full" value={state.value[4]} oninput={actions.oninput} onblur={actions.onblur} placeholder={_T('Chiyoda mansion 8F')} invalid={!!state.message} xclass={`wqe-belongs-attr wqe-belongs-address wqe-for-room wq_attr-${item.id}`} />
    </Control>
  )
}
attrItem_table.Address.initialState = (_item) => ({message:null, value:["", "", "", "", ""]})
attrItem_table.Address.compile = (_item, state) => {
  if (state.value.every(emptyString)) {
    return ""
  } else {
    return `${state.value[0]} ${state.value[1]}${state.value[2]}${state.value[3]} ${state.value[4]}`
  }
}
attrItem_table.Address.validate = (item, {value, message}) => {
  if (!item.required && value.every(emptyString)) {
    // thru
  } else if (value[0] == "" || value[1] == "" || value[2] == "" || value[3] == "") {
    return {value, message:MSG_REQUIRED}
  } else if (! value[0].match(new RegExp(_T('^[0-9]{3}-?[0-9]{4}$')))) {
    return {value, message:MSG_INVALID}
  }
  return {value, message:null}
}
attrItem_table.Address.createActions = (item) => {
  return {
    oninput: (ev) => (state, actions) => {
      const idx = branchNo(ev.currentTarget.name, '-')
      const curval = ev.currentTarget.value
      if (item.autoFill == 'yubinbango' && idx == 0 && curval.match(new RegExp(_T('^[0-9]{3}-?[0-9]{4}$')))) {
        window.requestAnimationFrame(() => complementAddress(curval, actions.onfill))
      }
      return {...state, value:replaceElement(state.value, idx, curval)}
    }, 
    onblur: (ev) => ({value, message}, actions) => {
      const idx = branchNo(ev.currentTarget.name, '-')
      if (idx == 4) {
        if (!item.required && value.every(emptyString)) {
          // thru
        } else if (value[0] == "" || value[1] == "" || value[2] == "" || value[3] == "") {
          return {value, message:MSG_REQUIRED}
        } else if (! value[0].match(new RegExp(_T('^[0-9]{3}-?[0-9]{4}$')))) {
          return {value, message:MSG_INVALID}
        }
      }
      return {value, message:null}
    }, 
    onfill: (fs) => ({value, message}, actions) => {
      value = [value[0], fs.region, fs.locality, fs.street, value[4]]
      return {value, message}
    }
  }
}

// {type, id, name, required, note}
attrItem_table.Checkbox = {}
attrItem_table.Checkbox.view = (item, state, actions) => {
  const id = `wqe-attr-checkbox-${item.id}`
  const name = `checkbox-${item.id}`
  return (
    <Control label="" required={false} message={_Tv(state.message)} note={item.note} requiredText={_Tv(item.required ? 'required' : 'optional')} id={id} xclass="wqe-belongs-attr wqe-for-checkbox" key={id}>
      <InputGroup xclass="wqe-belongs-attr wqe-belongs-checkbox">
        <Checkbox name={name} value="true" checked={state.checked} invalid={!!state.message} onchange={actions.onchange} xclass={`wqe-belongs-attr wqe-belongs-checkbox wq_attr-${item.id}`}>{item.name}</Checkbox>
      </InputGroup>
    </Control>
  )
}
attrItem_table.Checkbox.initialState = (item) => ({checked:!!item.initialValue, message:null})
attrItem_table.Checkbox.compile = (_item, state) => (state.checked ? _Tv('Checked') : '')
attrItem_table.Checkbox.validate = (item, state) => {
  if (item.required && !state.checked) {
    return {checked:state.checked, message:MSG_TOCHECK}
  }
  return (state.message == null) ? state : {checked:state.checked, message:null}
}
attrItem_table.Checkbox.createActions = (item) => {
  return {
    onchange: (ev) => ({checked, message}, actions) => {
      if (ev.currentTarget.checked) {
        return {checked:true, message:null}
      } else if (item.required) {
        return {checked:false, message:MSG_TOCHECK}
      } else {
        return {checked:false, message:null}
      }
    }
  }
}

// {type, id, name, required, note, options}
attrItem_table.Radio = {}
attrItem_table.Radio.view = (item, state, actions) => {
  const id = `wqe-attr-radio-${item.id}`
  const name = `radio-${item.id}`
  return (
    <Control label={item.name} required={item.required} message={_Tv(state.message)} note={item.note} requiredText={_Tv(item.required ? 'required' : 'optional')} id={id} xclass="wqe-belongs-attr wqe-for-radio" key={id}>
      <InputGroup gutter="mini" xclass="wqe-belongs-attr wqe-belongs-radio">
        {item.options.map((opt, i) => {
          return (
            <RadioButton name={name} value={opt} checked={state.value == opt} invalid={!!state.message} onchange={actions.onchange} index={i} xclass={`wqe-belongs-attr wqe-belongs-radio wq_attr-${item.id}`}>{opt}</RadioButton>
          )
        })}
      </InputGroup>
    </Control>
  )
}
attrItem_table.Radio.initialState = (item) => {
  const vs = item.options.filter(o => o == item.initialValue)
  return {value:vs.length > 0 ? vs[0] : null, message:null}
}
attrItem_table.Radio.compile = (_item, state) => state.value
attrItem_table.Radio.validate = (item, state) => {
  if (!item.required && state.value == null) {
    // thru
  } else if (state.value == null) {
    return {value:state.value, message:MSG_TOSELECT}
  } else if (indexOf(state.value, item.options) == -1) {
    return {value:state.value, message:MSG_INVALID}
  }
  return {value:state.value, message:null}
}
attrItem_table.Radio.createActions = (item) => {
  return {
    onchange: (ev) => ({value, message}, actions) => {
      if (ev.currentTarget.checked) {
        const value = ev.currentTarget.value || null
        reserveProcess()
        return {value, message:null}
      }
    }
  }
}

// {type, id, name, required, note, options}
attrItem_table.Dropdown = {}
attrItem_table.Dropdown.view = (item, state, actions) => {
  const id = `wqe-attr-dropdown-${item.id}`
  const name = `dropdown-${item.id}`
  const placeholder = _T('Please select')
  return (
    <Control label={item.name} required={item.required} message={_Tv(state.message)} note={item.note} requiredText={_Tv(item.required ? 'required' : 'optional')} id={id} xclass="wqe-belongs-attr wqe-for-dropdown" key={id}>
      <Select name={name} options={item.options} value={state.value} invalid={!!state.message} onchange={actions.onchange} xclass={`wqe-belongs-attr wqe-belongs-select wq_attr-${item.id}`} placeholder={placeholder} clearable={!item.required} />
    </Control>
  )
}
attrItem_table.Dropdown.initialState = attrItem_table.Radio.initialState
attrItem_table.Dropdown.compile = attrItem_table.Radio.compile
attrItem_table.Dropdown.validate = attrItem_table.Radio.validate
attrItem_table.Dropdown.createActions = (item) => {
  return {
    onchange: (ev) => ({_value, message}, actions) => {
      const value = ev.currentTarget.value || null
      reserveProcess()
      return {value, message: (!value && item.required) ? MSG_TOSELECT : null}
    }
  }
}

// {type, id, name, required, note, options, initialValue}
attrItem_table.MultiCheckbox = {}
attrItem_table.MultiCheckbox.view = (item, state, actions) => {
  const id = `wqe-attr-multicheckbox-${item.id}`
  const name = `multicheckbox-${item.id}`
  return (
    <Control label={item.name} required={item.required} message={_Tv(state.message)} note={item.note} requiredText={_Tv(item.required ? 'required' : 'optional')} id={id} xclass="wqe-belongs-attr wqe-for-multicheckbox" key={id}>
      <InputGroup gutter="mini" xclass="wqe-belongs-attr wqe-belongs-multicheckbox">
        {item.options.map((opt, i) => {
          return (
            <Checkbox id={`${id}-${i}`} name={name} value={opt} checked={indexOf(opt, state.value) != -1} invalid={!!state.message} onchange={actions.onchange} xclass={`wqe-belongs-attr wqe-belongs-multicheckbox wq_attr-${item.id}`}>{opt}</Checkbox>
          )
        })}
      </InputGroup>
    </Control>
  )
}
attrItem_table.MultiCheckbox.initialState = (item) => {
  return {value:item.initialValue, message:null}
}
attrItem_table.MultiCheckbox.compile = (_item, state) => state.value
attrItem_table.MultiCheckbox.validate = (item, state) => {
  if (!item.required && state.value.length == 0) {
    // thru
  } else if (state.value.length == 0) {
    return {value:state.value, message:MSG_TOSELECT}
  }
  return {value:state.value, message:null}
}
attrItem_table.MultiCheckbox.createActions = (item) => {
  return {
    onchange: (ev) => ({value, message}, actions) => {
      const v = ev.currentTarget.value
      return {
        value: ev.currentTarget.checked ? [...value, v] : value.filter(v0 => v0 != value), 
        message: null
      }
    }
  }
}

// {type, id, name, required, note, size, placeholder, multiline}
attrItem_table.Text = {}
attrItem_table.Text.view = (item, state, actions) => {
  const id = `wqe-attr-text-${item.id}`
  const name = `text-${item.id}`
  if (item.multiline) {
    return (
      <Control label={item.name} required={item.required} message={_Tv(state.message)} note={item.note} requiredText={_Tv(item.required ? 'required' : 'optional')} id={id} xclass={`wqe-belongs-attr wqe-for-text wqe-has-textarea`} key={id}>
        <TextArea size={item.size} name={name} placeholder={item.placeholder} value={state.value} invalid={!!state.message} oninput={actions.oninput} onblur={actions.onblur} xclass={`wqe-belongs-attr wqe-belongs-text wq_attr-${item.id}`} /> 
      </Control>
    )
  } else {
    return (
      <Control label={item.name} required={item.required} message={_Tv(state.message)} note={item.note} requiredText={_Tv(item.required ? 'required' : 'optional')} id={id} xclass={`wqe-belongs-attr wqe-for-text`}>
        <TextInput type="text" size={item.size} name={name} placeholder={item.placeholder} value={state.value} invalid={!!state.message} oninput={actions.oninput} onblur={actions.onblur} xclass={`wqe-belongs-attr wqe-belongs-text wq_attr-${item.id}`} /> 
      </Control>
    )
  }
}
attrItem_table.Text.initialState = (_item) => ({value:"", message:null})
attrItem_table.Text.compile = (_item, state) => state.value
attrItem_table.Text.validate = (item, state) => {
  if (item.required && state.value == "") {
    return {value:state.value, message:MSG_REQUIRED}
  }
  return (state.message == null) ? state : {value:state.value, message:null}
}
attrItem_table.Text.createActions = (item) => {
  return {
    oninput: (ev) => (state, _actions) => {
      return {...state, value:ev.currentTarget.value}
    }, 
    onblur: (ev) => (state, _actions) => {
      if (item.required && state.value == "") {
        return {value:state.value, message:MSG_REQUIRED}
      } else {
        return {value:state.value, message:null}
      }
    }
  }
}

// {id, type, action, siteKey}
attrItem_table.reCAPTCHA3 = {}
attrItem_table.reCAPTCHA3.view = (item, state, actions) => null
attrItem_table.reCAPTCHA3.initialState = item => {
  window.setTimeout(() => {
    // Gutenberg executes this code *before* document.body initialized.
    // So defer its execution.
    var e = document.createElement("script");
    e.setAttribute('src', `https://www.google.com/recaptcha/api.js?render=${item.siteKey}`)
    document.body.appendChild(e)
  }, 1000)
  
  return {value:"", message:null}
}
attrItem_table.reCAPTCHA3.compile = (item, _state) => {
  return (postfix) => {
    grecaptcha.execute(item.siteKey, {action: item.action}).then(token => {
      postfix(token)
    })
  }
}
attrItem_table.reCAPTCHA3.validate = (item, state) => {
  return state
}
attrItem_table.reCAPTCHA3.createActions = (item) => {{}}


const assembleAttrItems = (items) => {
  return items.reduce(({state, actions}, item) => {
    const s = attrItem_table[item.type].initialState(item)
    const a = attrItem_table[item.type].createActions(item)
    return {actions:{...actions, [item.id]:a}, state:{...state, [item.id]:s}}
  }, {actions:{}, state:{}})
}

const viewAttrItems = (state, actions) => {
  return (
    <div class="wqe-Attributes">
      {form.attrItems.map(item => {
        return attrItem_table[item.type].view(item, state[item.id], actions[item.id])
      })}
    </div>
  )
}
const viewAttrItemsToConfirm = (state, actions) => {
  return (
    <div class="wqe-Attributes wqe-is-confirming">
      {form.attrItems.map(item => {
        if (item.type == 'reCAPTCHA3') return null
        const id = `wqe-attr-${item.type.toLowerCase()}-${item.id}`
        return (
          <Control label={item.name} required={item.required} id={id} key={id}>
            <Echo value={attrItem_table[item.type].compile(item, state[item.id])} xclass={`wqe-belongs-attr wq_attr-${item.id}`} glue={_T(', ')}></Echo>
          </Control>
        )
      })}
    </div>
  )
}

const validateAttrData = (state) => {
  return form.attrItems.reduce((state, item) => {
    const s = attrItem_table[item.type].validate(item, state[item.id])
    if (s === state[item.id]) return state
    return {...state, [item.id]:s}
  }, state)
}


/*
 * ===============================================================
 * domain
 */

// Catalog = string{}
// SelectOption = {id:number, image:?string, name:string, note:?string, normalPrice:?number, price:number, labels:any{}, depends:any{}}
// Select = {type:'Select', id:number, image:?string, name:string, note:?string, multiple:bool, options:SelectOption[]}
// Hidden = {type:'Hidden', id:number, image:?string, category:?string, name:string, price:number, depends:any{}}
// Item = Select | Hidden
// Form = {id:number, items:Item[]}
// SelectedOptions = any{}
// Input = SelectedOptions
// State = {inputs:Input{}, labels:any{}}
// Detail = {category:?string, name:string, unitPrice:number, quantity:number}


const createDetail = (key, category, name, quantity, unitPrice, taxRate, operatable) => {
  return {key, category, name, quantity, unitPrice, taxRate, operatable}
}

const reserveProcess = () => {
  window.requestAnimationFrame(allActions.onprocess)
}
const onprocess = () => (state, actions) => {
  const attrs = form.attrItems.reduce((cur, item) => {
    const value = attrItem_table[item.type].compile(item, state.attrs[""+item.id])
    cur[""+item.id] = value
    return cur
  }, {})

  const {inputs, ...rest} = process(state.details.data, attrs)
  const details = {...state.details, ...rest, data:inputs}
  return {...state, details}
}
const process = (inputs, attrs) => {
  const {refTotal:_unused, ...rv} = form.detailItems.reduce((cur, item) => {
    return processTable[item.type](cur, item, attrs)
  }, {inputs, labels:{}, details:[], total:0, refTotal:0, messages:[]})

  if (rule.taxIncluded) return rv

  const subtotals = rv.details.reduce((subtotals, detail) => {
    const key = detail.taxRate === null ? "" : (""+detail.taxRate)
    const subtotal = (
      subtotals.hasOwnProperty(key) ? subtotals[key] : 0
    ) + normalizePrice(rule, detail.unitPrice * detail.quantity)
    return {...subtotals, [key]:subtotal}
  }, {})

  const taxes = reduceHash((cur, key, subtotal) => {
    const taxRate = key === "" ? rule.taxRate : key
    const tax = normalizePrice(rule, subtotal * taxRate * 0.01)
    return {...cur, [key]:tax}
  }, {}, subtotals)

  return {...rv, subtotals, taxes}
}
const processTable = {
  Group: (cur, item, attrs) => {
    if (! cur.inputs.hasOwnProperty(item.id)) {
      return cur
    }
    return item.products.reduce((cur, product) => {
      // We check because there can be a case where the option was cleared and the item had no selection.
      if (! cur.inputs.hasOwnProperty(item.id)) {
        return cur
      }
      const selectedProducts = cur.inputs[item.id]
      if (! selectedProducts.hasOwnProperty(product.id)) {
        return cur
      }
      const quantity = selectedProducts[product.id]
      if (quantity == 0) {
        return cur
      }
      const detail = createDetail(`Product-${item.id}-${product.id}`, item.name, product.name, quantity, product.price, product.taxRate, true)
      const details = [...cur.details, detail]
      const nprice = normalizePrice(rule, detail.unitPrice * detail.quantity)
      const total = cur.total + nprice
      const refTotal = cur.refTotal + nprice
      return {...cur, details, total, refTotal}
    }, cur)
  }, 
  Auto: (cur, item, attrs) => {
    if (! subsetOf(cur.labels, item.depends)) {
      return cur
    }
    const detail = createDetail(`Auto-${item.id}`, item.category, item.name, 1, item.price, item.taxRate, false)
    const details = [...cur.details, detail]
    const nprice = normalizePrice(rule, detail.unitPrice * detail.quantity)
    const total = cur.total + nprice
    const refTotal = cur.refTotal + nprice
    return {...cur, details, total, refTotal}
  }, 
  PriceWatcher: (cur, item, attrs) => {
    if (! compare2(cur.total, item.lower, item.lowerIncluded, item.higher, item.higherIncluded)) {
      return cur
    }
    const labels = {...cur.labels, ...item.labels}
    return {...cur, labels}
  }, 
  Stop: (cur, item, attrs) => {
    if (subsetOf(cur.labels, item.depends)) {
      const messages = [...cur.messages, item.message]
      return {...cur, messages}
    }
    return cur
  }
}


//const validateDetailDataForItem = (detailData, item) => {
//  const msg = validate_table[item.type](detailData, item)
//  return msg
//}
//const validateDetailData = (detailData) => {
//  return form.detailItems.reduce((messages, item) => {
//    const message = validate_table[item.type](detailData, item)
//    if (message) {
//      return {...messages, [item.id]:message}
//    } else {
//      return messages
//    }
//  }, {})
//}
//const validate_table = {
//  Group: (detailData, item) => null, 
//  Auto: (detailData, item) => null, 
//  PriceWatcher: (detailData, item) => null
//}

const detailActions = {
  decr: (ev) => (state, actions) => {
    const [_unused, gid, pid] = ev.currentTarget.name.split('-')
    const selectedProducts0 = state.data.hasOwnProperty(gid) ? state.data[gid] : {}
    const quantity0 = selectedProducts0.hasOwnProperty(pid) ? selectedProducts0[pid] : 0
    const quantity = (quantity0 <= 0) ? 0 : quantity0 - 1
    const selectedProducts = {...selectedProducts0, [pid]:quantity}
    const data = {...state.data, [gid]:selectedProducts}
    reserveProcess()
    return {...state, data}
  }, 
  incr: (ev) => (state, actions) => {
    const [_unused, gid, pid] = ev.currentTarget.name.split('-')
    const selectedProducts0 = state.data.hasOwnProperty(gid) ? state.data[gid] : {}
    const quantity0 = selectedProducts0.hasOwnProperty(pid) ? selectedProducts0[pid] : 0
    const quantity = quantity0 + 1
    const selectedProducts = {...selectedProducts0, [pid]:quantity}
    const data = {...state.data, [gid]:selectedProducts}
    reserveProcess()
    return {...state, data}
  }, 
  decrByMonitor: (ev) => (state, actions) => {
    const path = ev.currentTarget.name.split('-')
    const [_unused0, _unused1, gid, pid] = path
    const selectedProducts0 = state.data.hasOwnProperty(gid) ? state.data[gid] : {}
    const quantity0 = selectedProducts0.hasOwnProperty(pid) ? selectedProducts0[pid] : 0
    const quantity = (quantity0 <= 0) ? 0 : quantity0 - 1
    const selectedProducts = {...selectedProducts0, [pid]:quantity}
    const data = {...state.data, [gid]:selectedProducts}
    reserveProcess()
    return {...state, data}
  }, 
  incrByMonitor: (ev) => (state, actions) => {
    const path = ev.currentTarget.name.split('-')
    const [_unused0, _unused1, gid, pid] = path
    const selectedProducts0 = state.data.hasOwnProperty(gid) ? state.data[gid] : {}
    const quantity0 = selectedProducts0.hasOwnProperty(pid) ? selectedProducts0[pid] : 0
    const quantity = quantity0 + 1
    const selectedProducts = {...selectedProducts0, [pid]:quantity}
    const data = {...state.data, [gid]:selectedProducts}
    reserveProcess()
    return {...state, data}
  }
}


const createDetailState = (attrState) => {
  const attrs = form.attrItems.reduce((cur, item) => {
    const value = attrItem_table[item.type].compile(item, attrState[""+item.id])
    cur[""+item.id] = value
    return cur
  }, {})
  const {inputs:data, ...rest} = process({}, attrs)
  return {...rest, data}
}

const onback = (ev) => (state, actions) => {
  window.setTimeout(() => {
    scrollTo(`form-${form.id}`)
  }, 100)
  return {...state, viewMode:form.navigator}
}

/*const validateForm = (state) => {
  const detailMessages = validateDetailData(state.details.data)
  const attrs = validateAttrData(state.attrs)
  const attrMessages = reduceHash((cur, cid, stt) => {
    return (stt.message == null) ? cur : {...cur, [cid]:stt.message}
  }, {}, attrs)
  if (Object.keys(detailMessages).length || Object.keys(attrMessages).length) {
    // validation failed
    window.requestAnimationFrame(() => {
      if (Object.keys(detailMessages).length) focusErrorInput('wq_detail-', detailMessages)
      else focusErrorInput('wq_attr-', attrMessages)
    }, 100)
    const details = {...state.details, messages:detailMessages}
    return [false, {...state, details, attrs}]
  }
  // validation succeeded
  return [true, state]
}*/

const onsubmit = (ev) => (state, actions) => {
  // validate attrs
  const attrs = validateAttrData(state.attrs)
  const attrMessages = reduceHash((cur, cid, stt) => {
    return (stt.message == null) ? cur : {...cur, [cid]:stt.message}
  }, {}, attrs)
  if (state.details.messages.length || Object.keys(attrMessages).length) {
    // validation failed
    if (state.details.messages.length) {
      window.requestAnimationFrame(() => {
        window.alert(state.details.messages[0])
      })
    } else {
      window.requestAnimationFrame(() => {
        focusErrorInput('wq_attr-', attrMessages)
      })
    }
    return {...state, attrs}
  }

  let viewMode = (form.doConfirm && state.viewMode != 'confirm') ? 'confirm' : 'empty'
  //viewMode = filterByExt('nextViewMode', viewMode, ev)
  if (viewMode == 'confirm') {
    // show confirm view
    window.setTimeout(() => {
      scrollTo(`form-${form.id}`)
    }, 100)
    return {...state, viewMode:'confirm'}
  }

  //const submitType = filterByExt('nextSubmitType', 'submit', ev)
  const submitType = 'submit'
  const kontinue = (attrs) => {
    if (mode != 'execute') {
      window.requestAnimationFrame(() => {window.alert(_T('Processing stopped due to preview mode.'))});
      return;
    }

    const data = {
      formId: form.id, 
      details: state.details.data, 
      attrs
    }
    submit(data, actions.onsubmitK, submitType)
  }

  window.requestAnimationFrame(() => {
    let waitCount = 0
    let compiledAttrs = {}
    form.attrItems.forEach(item => {
      const val = attrItem_table[item.type].compile(item, attrs[item.id])
      if (typeof val == 'function') {
        const postfix = (dataValue) => {
          waitCount--
          compiledAttrs[item.id] = dataValue
          if (waitCount == 0) kontinue(compiledAttrs)
        }
        waitCount++
        val(postfix)
      } else {
        compiledAttrs[item.id] = val
      }
    })
    if (waitCount == 0) kontinue(compiledAttrs)
  })
  
  return {...state, loading:true}
}

const onsubmitK = ([resp, submitType]) => (state, actions) => {
  //console.log('onsubmitK', resp, submitType)
  if (submitType == 'confirm') {
    window.requestAnimationFrame(() => {
      window.open(resp)
    });
    return {...state, loading:false}
  } else {
    window.requestAnimationFrame(() => {
      if (form.thanksUrl) {
        window.location.href = form.thanksUrl
      } else {
        window.alert(_T('The form has been successfully submitted.'))
      }
    })
    // we dont set state.loading to `false`.
    //return {...state, loading:false}
  }
}

const onHideMonitor = (ev) => (state, actions) => {
  resumeOtherSurfaces();
  return {...state, spMonitorShown:false}
}
const onShowMonitor = (ev) => (state, actions) => {
  suspendOtherSurfaces();
  return {...state, spMonitorShown:true}
}

const findNextIndex = (current, labels) => {
  const len = form.detailItems.length
  for (let i = 0; i < len; i++) {
    if (i <= current) continue;
    const item = form.detailItems[i]
    if (item.type == "Group") {
      if (item.products.length > 0) {
        return i
      }
      // thru
    }
  }
  return form.detailItems.length
}
const findFirstId = () => {
  const len = form.detailItems.length
  for (let i = 0; i < len; i++) {
    const item = form.detailItems[i]
    if (item.type == "Group") return item.id
  }
  return -1
}

/*const findPrevIndex = (current, labels) => {
  if (current <= 0) return -1
  const indice = form.detailItems.reduce((cur, item, i) => {
    if (current <= i) return cur
    if (item.type == "Group") {
      if (item.products.length > 0) {
        cur.push(i)
      }
    }
    return cur
  }, [])
  return (indice.length > 0) ? indice.pop() : -1
}*/

/*const onWizardOpen = (ev) => (state, actions) => {
  const wIndex = findNextIndex(-1, state.details.labels)
  return {...state, wIndex, wIndex2:wIndex, wizardOpen:true, wFlipped:false}
}
const onWizardClose = (ev) => (state, actions) => {
  return {...state, wIndex:-1, wIndex2:-1, wizardOpen:false}
}
const onWizardNext = (ev) => (state, actions) => {
  const item = form.detailItems[state.wIndex]
  const msg = validateDetailDataForItem(state.details.data, item)
  if (msg) {
    const messages = {...state.details.messages, [item.id]:msg}
    const details = {...state.details, messages}
    return {...state, details}
  }
  const wIndex = findNextIndex(state.wIndex, state.details.labels)
  if (wIndex == form.detailItems.length) {
    window.setTimeout(actions.onWizardClose, 800)
    window.setTimeout(() => {
      scrollTo('wqe-monitor')
    }, 800)
  } else {
    window.setTimeout(() => actions.onWizardNextK(wIndex), 400)
  }
  return {...state, wIndex, wFlipped:false}
}
const onWizardNextK = (wIndex2) => (state, actions) => {
  return {...state, wIndex2}
}
const onWizardPrev = (ev) => (state, actions) => {
  const wIndex = findPrevIndex(state.wIndex, state.details.labels)
  window.setTimeout(() => actions.onWizardPrevK(wIndex), 400)
  return {...state, wIndex, wFlipped:true}
}
const onWizardPrevK = (wIndex2) => (state, actions) => {
  return {...state, wIndex2}
}*/
const onSelectGroup = (ev) => (state, actions) => {
  //console.log('onSelectGroup', ev, state)
  const [unused_, gid] = ev.target.name.split('-')
  
  if (behavior.scrollOnGroupSelection) {
    window.setTimeout(() => {
      scrollTo(`form-${form.id}`, true)
    }, 100)
  }
  return {...state, currentId:gid}
}
const calcMonitorPos = () => {
  const container = document.getElementById(`aforms-eats-form-${form.id}`).children[0]
  const child = document.getElementById('wqe-monitor')
  const crect = container.getBoundingClientRect()
  const vtop = crect.top
  const vbot = crect.bottom
  const ch = child.getBoundingClientRect().height
  //console.log('onscroll', vtop, vbot, crect.left)
  if (vtop > 0) {
    // childをcontainerの上部にくっつける
    return {v:'top', left:Math.round(crect.width)}
  } else if (vbot < ch) {
    // childをcontainerの下部にくっつける
    return {v:'bottom', left:Math.round(crect.width)}
  } else {
    // childを画面にくっつける
    return {v:'screen', left:Math.round(crect.right)}
  }
}
const calcNavbarPos = () => {
  const container = document.getElementById(`aforms-eats-form-${form.id}`).children[0]
  const child = document.getElementById('wqe-navbar')
  const crect = container.getBoundingClientRect()
  const vtop = crect.top
  const vbot = crect.bottom
  const ch = child.getBoundingClientRect().height
  //console.log('onscroll', vtop, vbot, crect.left)
  if (vtop > 0) {
    // childをcontainerの上部にくっつける
    return {v:'top', left:0, width:crect.width}
  } else if (vbot < ch) {
    // childをcontainerの下部にくっつける
    return {v:'bottom', left:0, width:crect.width}
  } else {
    // childを画面にくっつける
    return {v:'screen', left:crect.left, width:crect.width}
  }
}
const onscroll = (ev) => (state, actions) => {
  if (state.viewMode == 'confirm') return null
  return {...state, monitorPos:calcMonitorPos(), navbarPos:calcNavbarPos()}
}
const onresize = (ev) => (state, actions) => {
    //console.log('action/onresize')
  if (state.viewMode == 'confirm') return null
  return {...state, monitorPos:calcMonitorPos(), navbarPos:calcNavbarPos()}
}

/*
 * ===============================================================
 * App
 */

const NavBar = ({state, max, current, pos, actions, navigator}) => {
  return (
    <div class={`wqe-NavBar ${state.viewMode == 'confirm' ? 'wqe-is-confirming' : ''} ${state.spMonitorShown ? 'wqe-is-monitor-shown' : ''} wqe-belongs-${navigator} wqe-sticks-${pos.v}`} style={{left:pos.left+"px", width:pos.width+"px"}} id="wqe-navbar" key="navbar">
      <div class="wqe--menuWrap">
        <ul class="wqe--menu">
          {form.detailItems.map((item) => {
            if (item.type == 'Group') {
              if (! item.visible || item.products.every(p => p.state == "hidden")) return null
              return (
                <li class={`wqe--item ${current == item.id ? 'wqe-is-current' : ''}`}>
                  <a href="javascript:void(0);" onclick={actions.onSelectGroup} name={`navbarmenuitem-${item.id}`}>
                    <div class="wqe--media">
                      <Image src={item.image} scaling="center" xclass={`wqe-belongs-${navigator} wqe-belongs-navbar`} />
                    </div>
                    <div class="wqe--main">
                      <div class="wqe--name">{item.name}</div>
                      <div class="wqe--note">{item.note}</div>
                    </div>
                  </a>
                </li>
              )
            } else {
              return null;
            }
          })}
        </ul>
      </div>
      <div class="wqe--commands">
        <Button type="normal" onclick={actions.onShowMonitor} xclass="wqe-belongs-navbar wqe-for-showmonitor"><span><span key={state.details.total} oncreate={tnOnCreate} onremove={tnOnRemove}>{pricePrefix}{nf(state.details.total)}{priceSuffix}</span></span></Button>
      </div>
    </div>
  )
}

const buildActionButtons = (state, actions) => {
  const submittable = form.attrItems.length > 0
  if (state.viewMode == 'confirm' && (!submittable || !form.doConfirm)) return []
  const intent = 
        (!submittable) ? 'empty' 
      : ((form.doConfirm && state.viewMode != 'confirm') ? 'confirm' : 'submit')
  const actionButtons = 
        (intent == 'submit') 
      ? [(<Button type="primary" onclick={actions.onsubmit} disabled={state.loading} xclass="wqe-belongs-action wqe-for-submit">{_Tv('Submit')}</Button>)]
      : ((intent == 'confirm') 
      ? [(<Button type="primary" onclick={actions.onsubmit} disabled={state.loading} xclass="wqe-belongs-action wqe-for-confirm">{_Tv('To Confirmation Screen')}</Button>)]
      : [])
  //const rv = filterByExt('viewActionButtons', actionButtons, intent)
  //console.log('buildActionButtons', intent, actionButtons, rv)
  return actionButtons
}

const view = (state, actions) => {
  const submittable = form.attrItems.length > 0
  const actionButtons = buildActionButtons(state, actions)
  return (
    <form class={`wqe-Form ${(state.viewMOde == 'confirm' ? 'wqe-is-confirming' : '')}`} id={`form-${form.id}`} novalidate>
      <input type="text" name="to-disable-auto-submission" style="display:none;" />
      {state.viewMode == 'confirm' ? (
        <div class="wqe--lead">
          <p class="wqe--leadText">{_Tv('Please check your entry.')}</p>
          <Button type="normal" onclick={actions.onback} disabled={state.loading} xclass="wqe-for-back">{_Tv('Back')}</Button>
        </div>
      ) : null}
      {state.viewMode == 'horizontal' ? (
        <HNavigator data={state.details.data} current={state.currentId} onproductdecr={actions.details.decr} onproductincr={actions.details.incr}>
          <NavBar state={state} actions={actions} current={state.currentId} pos={state.navbarPos} navigator="hnavigator"></NavBar>
        </HNavigator>
      ) : null}
      <Monitor detailsState={state.details} confirming={state.viewMode == 'confirm'} spShown={state.spMonitorShown} monitorPos={state.monitorPos} ondecr={actions.details.decrByMonitor} onincr={actions.details.incrByMonitor} onHide={actions.onHideMonitor} />
      {submittable ? (
        state.viewMode == 'confirm' ? viewAttrItemsToConfirm(state.attrs, actions.attrs) : viewAttrItems(state.attrs, actions.attrs)
      ) : null}
      {actionButtons.length > 0 ? (
        <Control label="" required={false} message={null} note={null} id="wqe-action" xclass="wqe-for-action">
          <InputGroup gutter="mini">
            {actionButtons}
          </InputGroup>
        </Control>
      ) : null}
    </form>
  )
}

// wqData = {form, controls, catalog, rule}
const form = wqData.form;
const catalog = wqData.catalog;
const rule = wqData.rule;
const behavior = wqData.behavior;
const mode = wqData.mode;
const submitUrl = wqData.submitUrl;
const confirmUrl = wqData.confirmUrl;
const [pricePrefix, priceSuffix] = catalog['$%s'].split('%s')

const init = () => {
  const {state:cState, actions:cActions} = assembleAttrItems(form.attrItems)
  const state = {
    details: createDetailState(cState), 
    attrs: cState, 
    viewMode: form.navigator,  // horizontal, confirm
    loading: false, 
    spMonitorShown: false, 
    monitorPos: {v:'top', left:5000}, 
    navbarPos: {v:'top', left:0, width:0}, 
    currentId: findFirstId(), 
  }
  window.setTimeout(() => allActions.onscroll(), 500)
  const actions = {
    details: detailActions, 
    attrs: cActions, 
    onback, 
    onsubmit, 
    onsubmitK, 
    onHideMonitor, 
    onShowMonitor, 
    /*onWizardClose, 
    onWizardOpen, 
    onWizardNext, 
    onWizardNextK, 
    onWizardPrev, 
    onWizardPrevK, */
    onSelectGroup, 
    onscroll, 
    onresize,
    onprocess
  }
  return app(state, actions, view, document.getElementById('aforms-eats-form-'+form.id))
}
const allActions = init();

window.setTimeout(() => {
  document.addEventListener('scroll', allActions.onscroll)
  window.addEventListener('resize', allActions.onresize)
}, 100)