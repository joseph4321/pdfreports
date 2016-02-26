# Zabbix Report Generation

This code was written to provide summary reports on a per property basis, and a general overview, of the network infrastructure.  The monitoring system being used is zabbix, and is collecting data mostly through snmp, although also utilizing the zabbix agent and the zabbix proxy.

Zabbix does not come with a built in report generation tool that can be used to create nicely formatted pdf's for managment, so I decided to write my own.  Zabbix provides a nice api through json that can be used to gather information or modify devices. 

Demo video of the reports can be found <a href="https://www.youtube.com/watch?v=Dic1zELRZzo">here</a>

Programming languages used: perl (gather json information), php (pdf report generation)
OS: CentOS 6.3

<img src="https://raw.githubusercontent.com/joseph4321/pdfreports/master/shot1.png" alt="Drawing" style="width: 400px;height: 400px"/>
<img src="https://raw.githubusercontent.com/joseph4321/pdfreports/master/shot1.png" alt="Drawing" style="width: 400px;height: 400px"/>
