Dumper
======

*extended and well formated var_dump for PHP*

For all the people who dont like or can't debugging their php code, but do depend on additional information about the running code and want also a pretty printed version of a state, not like var_dump() or die().
Here it is!

The Dumper Class
----------------

###Call var dump:###
``` php
\Dumper\Dumper::dump($data, $collapsed = false, $detailed = true);
```

**$data** : a variable. You can use all types! Basically the data you want to watch

**$collapsed** : if true, the output will be hidden and so you can click on a scope to show the childs

**$detailed** : any information are more excactly. for instance strings, that conatins links, will be printed in an a tag. (strings are limited to 80 chars)
  
  
  
  
**to add information which methods a dumped object has, try dumpReflection instead:**
``` php
\Dumper\Dumper::dumpReflection($data, $collapsed = false, $detailed = true);
```

![Example](https://docs.google.com/file/d/0B7szYi0UM56SckFPMEhraTB4dDg/preview)

Contributions welcome!
