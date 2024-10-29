<?php

namespace AFormsEats\Domain;

class FormProcessor 
{
    // set to comma-separated-fields
    protected function decompileSet($set) 
    {
        if (! $set) {
            return '';
        }
        return join(', ', array_keys(get_object_vars($set)));
    }

    // comma-separated-fields to set
    protected function compileSet($csv) 
    {
        $set = new \stdClass();
        if (! $csv) {
            return $set;
        }

        $fields = explode(',', $csv);
        foreach ($fields as $field) {
            $field = trim($field);
            $set->{$field} = true;
        }
        return $set;
    }

    // string[] to comma-separated-fields
    protected function decompileStrings($strs) 
    {
        if (! $strs) {
            return '';
        }
        return join(', ', $strs);
    }

    // comma-separated-fields to string[]
    protected function compileStrings($csv) 
    {
        $ss = array();
        if (! $csv) {
            return $ss;
        }

        $fields = explode(',', $csv);
        foreach ($fields as $field) {
            $field = trim($field);
            $ss[] = $field;
        }
        return $ss;
    }

    // vdom to html
    protected function decompileVdom($vdom) 
    {
        if (is_null($vdom)) {
            return '';
        
        } else if (is_string($vdom)) {
            return esc_html($vdom);
        
        } else if (is_array($vdom)) {
            $content = '';
            foreach ($vdom as $child) {
                $content .= $this->decompileVdom($child);
            }
            return $content;

        } else if (is_object($vdom)) {
            $attrs = '';
            foreach (get_object_vars($vdom->attributes) as $name => $value) {
                if (is_bool($value)) {
                    if ($value) {
                        $attrs .= sprintf(' %s', esc_attr($name));
                    }
                } else {
                    $attrs .= sprintf(' %s="%s"', esc_attr($name), esc_attr($value));
                }
            }
            $content = '';
            foreach ($vdom->children as $child) {
                $content .= $this->decompileVdom($child);
            }

            return sprintf('<%s%s>%s</%s>', 
                           $vdom->nodeName, 
                           $attrs, 
                           $content, 
                           $vdom->nodeName);
        }
    }

    protected function convertNode($node) 
    {
        switch ($node->nodeType) {
            case XML_TEXT_NODE: 
                return $node->textContent;
            
            case XML_ELEMENT_NODE: 
                $attrMap = new \stdClass();
                foreach ($node->attributes as $attr) {
                    if ($attr->name == $attr->value) {
                        $attrMap[$attr->name] = true;
                    } else {
                        $attrMap->{$attr->name} = $attr->value;
                    }
                }
                $children = array();
                for ($c = $node->firstChild; $c != null; $c = $c->nextSibling) {
                    $children[] = $this->convertNode($c);
                }
                $rv = new \stdClass();
                $rv->nodeName = $node->tagName;
                $rv->attributes = $attrMap;
                $rv->children = $children;
                return $rv;
            
            default: 
                return $node->textContent;
        }
    }

    // html to vdom
    protected function compileVdom($html) 
    {
        $html = '<html><body>'.$html.'</body></html>';
        $doc = new \DOMDocument();
        //$options = LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD|LIBXML_NONET|LIBXML_NOWARNING;
        $options = LIBXML_NONET|LIBXML_NOWARNING;
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $flag = libxml_use_internal_errors(true);
        if (! $doc->loadHTML($html, $options)) {
            libxml_clear_errors();
            libxml_use_internal_errors($flag);
            throw new \RuntimeException('html parsing failure');
        }
        libxml_clear_errors();
        libxml_use_internal_errors($flag);
        $node = $doc->documentElement;

        $vdom = $this->convertNode($node);
        return $vdom->children[0]->children;
    }

    protected function compileUrl($url) 
    {
        $revdb = array(
            // unescaped chars
            '%2D'=>'-','%5F'=>'_','%2E'=>'.','%21'=>'!', '%7E'=>'~',
            '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')', 
            // reserved chars
            '%3B'=>';','%2C'=>',','%2F'=>'/','%3F'=>'?','%3A'=>':',
            '%40'=>'@','%26'=>'&','%3D'=>'=','%2B'=>'+','%24'=>'$', 
            // score
            '%23'=>'#'
        );
        return strtr(rawurlencode($url), $revdb);
    }

    protected function decompileUrl($url) 
    {
        return rawurldecode($url);
    }

    public function decompile($form) 
    {
        if ($form->thanksUrl) {
            $form->thanksUrl = $this->decompileUrl($form->thanksUrl);
        }

        foreach ($form->detailItems as $item) {
            switch ($item->type) {
                case 'Auto': 
                    $item->depends = $this->decompileSet($item->depends);
                    break;
                case 'Selector': 
                    $item->note = $this->decompileVdom($item->note);
                    foreach ($item->options as $option) {
                        $option->labels = $this->decompileSet($option->labels);
                        $option->depends = $this->decompileSet($option->depends);
                        $option->note = $this->decompileVdom($option->note);
                    }
                    break;
                case 'Group': 
                    $item->note = $this->decompileVdom($item->note);
                    foreach ($item->products as $product) {
                        $product->note = $this->decompileVdom($product->note);
                    }
                    break;
                case 'PriceChecker': /* thru */
                case 'PriceWatcher': 
                    $item->labels = $this->decompileSet($item->labels);
                    break;
                case 'Quantity': 
                    $item->note = $this->decompileVdom($item->note);
                    $item->depends = $this->decompileSet($item->depends);
                    break;
                case 'Stop': 
                    $item->depends = $this->decompileSet($item->depends);
                    break;
            }
        }

        foreach ($form->attrItems as $item) {
            if ($item->type != 'reCAPTCHA3') {
                $item->note = $this->decompileVdom($item->note);
            }
            switch ($item->type) {
                case 'Radio': /* thru */
                case 'Dropdown': 
                    $item->options = $this->decompileStrings($item->options);
                    break;
                case 'MultiCheckbox': 
                    $item->options = $this->decompileStrings($item->options);
                    $item->initialValue = $this->decompileStrings($item->initialValue);
                    break;
            }
        }
    }

    public function preprocess($form) 
    {
        // AjvとJsonSchemaではformat=uriのバリデーションが異なる。JsonSchemaは日本語を含むURLを受け付けない。
        if ($form->thanksUrl) {
            $form->thanksUrl = $this->compileUrl($form->thanksUrl);
        }
    }

    public function compile($form) 
    {
        foreach ($form->detailItems as $item) {
            switch ($item->type) {
                case 'Auto': 
                    $item->depends = $this->compileSet($item->depends);
                    break;
                case 'Selector': 
                    $item->note = $this->compileVdom($item->note);
                    foreach ($item->options as $option) {
                        $option->labels = $this->compileSet($option->labels);
                        $option->depends = $this->compileSet($option->depends);
                        $option->note = $this->compileVdom($option->note);
                    }
                    break;
                case 'Group': 
                    $item->note = $this->compileVdom($item->note);
                    foreach ($item->products as $product) {
                        $product->note = $this->compileVdom($product->note);
                    }
                    break;
                case 'PriceChecker': 
                case 'PriceWatcher': /* thru */
                    $item->labels = $this->compileSet($item->labels);
                    break;
                case 'Quantity': 
                    $item->note = $this->compileVdom($item->note);
                    $item->depends = $this->compileSet($item->depends);
                    break;
                case 'Stop': 
                    $item->depends = $this->compileSet($item->depends);
                    break;
            }
        }

        foreach ($form->attrItems as $item) {
            if ($item->type != 'reCAPTCHA3') {
                $item->note = $this->compileVdom($item->note);
            }
            switch ($item->type) {
                case 'Radio':  /* thru */
                case 'Dropdown': 
                    $item->options = $this->compileStrings($item->options);
                    break;
                case 'MultiCheckbox': 
                    $item->options = $this->compileStrings($item->options);
                    $item->initialValue = $this->compileStrings($item->initialValue);
                    break;
            }
        }
    }
}