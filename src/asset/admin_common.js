
import {h} from 'hyperapp';

export const tnOnCreate = (el) => {
  var tid = null
  el.classList.add('wq-is-created')
  window.setTimeout(() => {
    el.classList.add('wq-is-run')
    el.classList.remove('wq-is-created')
    el.addEventListener('transitionend', () => {
      el.classList.remove('wq-is-run')
      window.clearTimeout(tid)
    }, {once:true})
    tid = window.setTimeout(() => {
      el.classList.remove('wq-is-run')
    }, 3000)
  }, 50)
}

export const tnOnRemove = (el, done) => {
  var tid = null
  el.classList.add('wq-is-run')
  window.setTimeout(() => {
    el.classList.add('wq-is-removed')
    el.addEventListener('transitionend', () => {
      try {
        el.classList.remove('wq-is-run')
        window.clearTimeout(tid)
        done()
      } catch (ex) {
        // ignore
      }
    })
  }, 50)
  tid = window.setTimeout(() => {
    try {
      done()
    } catch (ev) {
      // ignore
    }
  }, 3000)
}

const extractMessage = e => {
  // 1つのプロパティに複数のエラーが出る場合がある。
  // その場合、最後のエラーを表示する。
  // ただし、anyOfに関するエラーは除外する。
  return e.message.indexOf('should match pattern') === 0 ? 'should match pattern' : 
         e.message === 'should match some schema in anyOf' ? null : 
         //e.message === 'should be equal to one of the allowed values' ? null : 
         e.message === 'should match exactly one schema in oneOf' ? null : e.message
}

export const createMessages = (es, excludedPaths = []) => {
  return es.reduce((ms, e) => {
    if (excludedPaths.indexOf(e.dataPath) != -1) return ms
    const msg = extractMessage(e)
    if (msg === null) return ms
    else return {...ms, [e.dataPath]:msg}
  }, {})
}

export const updateMessages = (ms, path, es) => {
  const es2 = (es === null) ? [] : es.filter(e => e.dataPath == path)
  if (es2.length == 0) {
    // delete
    const ms2 = {...ms}
    delete ms2[path]
    return ms2
  } else {
    // replace
    return es.reduce((ms, e) => {
      const msg = extractMessage(e)
      if (msg === null) return ms
      else return {...ms, [path]:msg}
    }, ms)
  }
}

const br = {nodeName:'br', attributes:{}, children:[]}

export const strToVdom = (x) => {
  const lines = x.split('\n')
  if (lines.length == 1) return lines[0]
  
  return lines.reduce((cur, line) => {
    return (cur.length == 0) ? [line] : [...cur, br, line]
  }, [])
}

export const translate = (catalog) => (x) => {
  if (typeof x == 'undefined') return null
  if (catalog.hasOwnProperty(x)) {
    return catalog[x]
  } else {
    console.log('TO TRANSLATE: ', x);
    return x;
  }
}

export const deepCopy = (x) => JSON.parse(JSON.stringify(x))

export const Message = (props, children) => {
  if (children.length == 0) return null
  if (props.hasOwnProperty('id')) {
    props.key = props.id
  }
  return (
    <div class="wq-Message" {...props}>{children}</div>
  )
}

export const focusErrorInput = (es) => {
  let name = null
  if (! Array.isArray(es)) {
    const ks = Object.keys(es)
    if (ks.length == 0) return;
    name = ks[0].slice(1)
  } else {
    if (es.length == 0) return;
    name = es[0].dataPath.slice(1)
  }
  const elems = document.getElementsByName(name);
  if (elems.length == 0) return;
  elems[0].focus();
}

export const mapHash = (f, hash) => {
  const rv = {}
  for (let p in hash) {
    rv[p] = f(p, hash[p])
  }
  return rv
}

export const reduceHash = (f, cur, hash) => {
  for (let key in hash) {
    cur = f(cur, key, hash[key])
  }
  return cur
}

export const branchNo = (name, sep) => {
  const off = name.lastIndexOf(sep)
  const fragment = name.slice(off + 1)
  return parseInt(fragment)
}

export const findByProp = (name, val, arr) => {
  const len = arr.length
  for (let i = 0; i < len; i++) {
    if (arr[i][name] == val) return arr[i]
  }
  return undefined
}

export const findIndexByProp = (name, val, arr) => {
  const len = arr.length
  for (let i = 0; i < len; i++) {
    if (arr[i][name] == val) return i
  }
  return -1
}

export const lmove = (arr, from, to) => {
  const item = arr[from]
  const arr2 = arr.filter((e, i) => i != from)
  arr2.splice(to, 0, item)
  return arr2
}

export const linsert = (arr, idx, e) => {
  const arr2 = arr.reduce((cur, e0, i) => {
    if (i == idx) {
      return [...cur, e, e0]
    } else {
      return [...cur, e0]
    }
  }, [])
  if (arr2.length == idx) {
    arr2.push(e)
  }
  return arr2
}

export const lremove = (arr, from) => {
  arr = arr.filter((e, i) => i != from)
  return arr
}

export const lreplace = (arr, idx, e) => {
  return arr.map((e0, i) => (i == idx) ? e : e0)
}

export const joinSet = (set, glue) => {
  let rv = ''
  let isTail = false
  for (let key in set) {
    if (isTail) rv += glue
    rv += key
    isTail = true
  }
  return rv
}

export const scrollToTop = () => {
  const c = document.documentElement.scrollTop || document.body.scrollTop;
  if (c > 0) {
    const diff = Math.max(c / 8, 4)
    window.requestAnimationFrame(scrollToTop);
    window.scrollTo(0, c - diff);
  }
};

export function sprintf(format) {
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

export function hremove(hash, key) {
  const copy = {...hash}
  delete copy[key]
  return copy
}