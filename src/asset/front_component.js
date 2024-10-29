import { h } from 'hyperapp';


export const tnOnCreate = (el) => {
  el.classList.add('wqe-is-created')
  el.classList.add('wqe-is-run')
  el.classList.add('wqe-for-created')
  window.setTimeout(() => {
    el.classList.remove('wqe-is-created')
    const h =  (ev) => {
      if (ev.target !== el) return;
      el.classList.remove('wqe-is-run')
      el.classList.remove('wqe-for-created')
      el.removeEventListener('transitionend', h)
    }
    el.addEventListener('transitionend', h)
    window.setTimeout(() => {
      el.classList.remove('wqe-is-run')
      el.classList.remove('wqe-for-created')
      el.removeEventListener('transitionend', h)
    }, 800)
  }, 100)
}

export const tnOnRemove = (el, done) => {
  el.classList.add('wqe-is-run')
  el.classList.add('wqe-for-removed')
  window.setTimeout(() => {
    var doneCalled = false
    el.classList.add('wqe-is-removed')
    el.addEventListener('transitionend', (ev) => {
      if (ev.target !== el) return;
      el.classList.remove('wqe-is-run')
      el.classList.remove('wqe-for-removed')
      if (! doneCalled) {
        done()
        doneCalled = true
      }
    })
    window.setTimeout(() => {
      el.classList.remove('wqe-is-run')
      el.classList.remove('wqe-for-removed')
      if (! doneCalled) {
        done()
        doneCalled = true
      }
    }, 800)
  }, 100)
}



export const Image = (
    {
      src, 
      scaling = 'center', 
      alt = '', 
      xclass = ''
    }) => {
  const style = {backgroundImage: "url("+src+")"}
  return (
    <div class={`wqe-Image wqe-scaling-${scaling} ${xclass}`} style={style}>
      <img src={src} alt={alt} class="wqe--img" />
    </div> 
  )
}

export const TextInput = (
    {
      type, 
      size,  // full(100%), nano(3em), mini(4em), small(7em), normal(11em)
      name, 
      placeholder, 
      value, 
      invalid = false, 
      oninput, 
      onblur, 
      xclass = ''
    }) => {
  const isInvalid = (invalid) ? 'wqe-is-invalid' : ''
  const id = `wqe-text-${name}`
  return (
    <input type={type} class={`wqe-TextInput wqe-size-${size} ${isInvalid} ${xclass}`} id={id} name={name} placeholder={placeholder} value={value} oninput={oninput} onblur={onblur} />
  )
}

export const TextArea = (
    {
      name, 
      placeholder, 
      value, 
      size = 'normal',  // full, normal, small, mini, nano
      invalid = false, 
      oninput, 
      onblur, 
      xclass = ''
    }) => {
  const isInvalid = (invalid) ? 'wqe-is-invalid' : ''
  const id = `wqe-textarea-${name}`
  return (
    <textarea class={`wqe-TextArea ${isInvalid} wqe-size-${size} ${xclass}`} id={id} name={name} placeholder={placeholder} value={value} oninput={oninput} onblur={onblur} />
  )
}

export const RadioButton = (
    {
      index, 
      name, 
      value, 
      checked, 
      invalid = false, 
      onchange, 
      xclass = ''
    }, children) => {
  const id = `wqe-radio-${name}-${index}`
  const isInvalid = (invalid) ? 'wqe-is-invalid' : ''
  return (
    <div class={`wqe-Radio ${xclass}`} id={id+'-wrapper'}>
      <input type="radio" name={name} value={value} checked={checked} onchange={onchange} id={id} class={`${isInvalid}`} />
      <label for={id}>{children}</label>
    </div>
  )
}

export const Checkbox = (
    {
      name, 
      value, 
      checked, 
      invalid = false, 
      onchange, 
      xclass = '', 
      id = null
    }, children) => {
  if (id === null) id = `wqe-checkbox-${name}`
  const isInvalid = (invalid) ? 'wqe-is-invalid' : ''
  return (
    <div class={`wqe-Checkbox ${xclass}`} id={id+'-wrapper'}>
      <input type="checkbox" name={name} value={value} checked={checked} onchange={onchange} id={id} class={`${isInvalid}`} />
      <label for={id}>{children}</label>
    </div>
  )
}

export const Select = (
    {
      name, 
      options, 
      value, 
      invalid = false, 
      disabled = false, 
      clearable = false, 
      onchange, 
      placeholder, 
      xclass = '', 
      labelFunc = null, 
      valueFunc = null, 
      enabledFunc = null, 
      key = null
    }) => {
  const id = `wqe-select-${name}`
  const isInvalid = (invalid) ? 'wqe-is-invalid' : ''
  placeholder = placeholder || ""
  return (
    <div class={`wqe-Select ${xclass}`} id={id+'-wrapper'} key={key}>
      <select class="wqe--input" name={name} onchange={onchange} id={id} class={`wqe--input ${isInvalid}`} disabled={disabled}>
        <option value="" disabled={!clearable} selected={!value}>{placeholder}</option>
        {labelFunc ? (
          options.map(o => {
            const v = valueFunc(o)
            return (
              <option value={v} selected={v == value} disabled={!enabledFunc(o)}>{v}</option>
            )
          })
        ) : (
          options.map(o => (
            <option value={o} selected={o == value}>{o}</option>
          ))
        )}
      </select>
    </div>
  )
}

export const Echo = (
    {
      name, 
      value, 
      glue, 
      xclass = ''
    }) => {
  const id = `wqe-echo-${name}`
  value = (Array.isArray(value)) ? value.join(glue) 
        : (value === null) ? ''
        : value
  const lines = value.split(/\r?\n/).reduce((result, line) => {
    result.push(line)
    result.push(<br></br>)
    return result
  }, [])
  return (
    <div class={`wqe-Echo ${xclass}`} id={id}>{lines}</div>
  )
}

export const Button = (
    {
      type = 'normal',  // normal, primary
      disabled = false, 
      xclass = '', 
      onclick, 
      name = null
    }, children) => {
  return (
    <button type="button" name={name} class={`wqe-Button wqe-type-${type} ${xclass}`} disabled={disabled} onclick={onclick} >{children}</button>
  )
}

export const InputGroup = (
    {
      gutter = 'none',  // none, small, mini
      xclass = '', 
    }, children) => {
  return (
    <div class={`wqe-InputGroup wqe-gutter-${gutter} ${xclass}`}>{children}</div>
  )
}

export const Control = (
    {
      label, 
      required, 
      message, 
      note, 
      requiredText, 
      xclass = '', 
      id = null, 
      key = null
    }, input) => {
  return (
    <div class={`wqe-Control wqe-lct-enabled ${xclass}`} id={id} oncreate={tnOnCreate} onremove={tnOnRemove} key={key}>
      <div class={`wqe--header ${label ? '' : 'wqe-content-empty'}`}>
        <span class="wqe--label">{label}</span>
        <span class={`wqe--required ${!required ? 'wqe-is-optional' : ''}`}>{requiredText}</span>
      </div>
      <div class="wqe--body">
        {input}
        {(note) ? (<div class="wqe--note">{note}</div>) : null}
        {(message) ? (<div class="wqe--message wqe-lct-enabled" oncreate={tnOnCreate} onremove={tnOnRemove}>{message}</div>) : null}
      </div>
    </div>
  )
}
