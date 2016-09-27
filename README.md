# Klear

Klear is modular a Zend Framework 1 frontend

## Installing

### Sources
You can find install instructions in [klear-tutorial](http://irontec.github.io/klear-tutorial/index.html) documentation (in Spanish).

### Binaries
Klear is also distributed as binary packages for Debian through Irontec repository

* Add Irontec repositories entry in your /etc/apt/sources.list 

  ```
   deb http://packages.irontec.com/debian chloe main
  ```

* Add Irontect repositories key

   ```
    wget http://packages.irontec.com/public.key -q -O - | apt-key add -
   ```

* Install chloe release packages (and any required extra klear module)

   ```
    apt-get update
    apt-get install klear klear-matrix klear-library
  ```

## License

[EUPL v1.1](https://github.com/irontec/android-kotlin-samples/blob/master/LICENSE.txt)

```
Copyright 2012-2016 Irontec SL

Licensed under the EUPL, Version 1.1 or - as soon they will be approved by the European
Commission - subsequent versions of the EUPL (the "Licence"); You may not use this work
except in compliance with the Licence.

You may obtain a copy of the Licence at:
http://ec.europa.eu/idabc/eupl.html

Unless required by applicable law or agreed to in writing, software distributed under 
the Licence is distributed on an "AS IS" basis, WITHOUT WARRANTIES OR CONDITIONS OF 
ANY KIND, either express or implied. See the Licence for the specific language 
governing permissions and limitations under the Licence.
```
