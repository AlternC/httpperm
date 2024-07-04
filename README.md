# HTTPPERM

an OUTGOING http/https filtering/logging interface
for AlternC hosting control panel


## Configuration

Once pacakge retrieved and installed, you need to enable iptables rules
* Check [httpperm-iptables-script.sh](httpperm-iptables-script.sh) script
* Adapt it
* Deploy it
* Enjoy

## Get the package

### Build own package

You can compile this package with:

```
    apt install build-essential debhelper git
    git clone https://github.com/AlternC/alternc-httperm/
    cd alternc-httperm
    dpkg-buildpackage -us -uc
```

### From GitHub

You can obtain nightly and last stable package from the dedicated page : [releases page](https://github.com/AlternC/alternc-httperm/releases)

### From our repository

You can get package from our official repository : [debian.alternc.org](https://debian.alternc.org/)

## Dependency

You need an AlternC 3.3.* (only).

## How to use

This package supports only AlternC 3.3.
Original code was set to AlternC 3.3 and could be deployed manually

