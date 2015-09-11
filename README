# guacamole-auth-userfiles

## Description

This project is a plugin for [Guacamole](http://guac-dev.org), an HTML5 based
remote desktop solution supporting VNC/RFB, RDP, and SSH.

This plugin is an _authentication provider_ that enables stateless, on-the-fly
configuration of remote desktop connections that are authorized using a
pre-shared key. It is most appropriate for scenarios where you have an existing
user authentication & authorization mechanism.

This is tested with guacamole 0.9.7 - 0.9.8

## Building

guacamole-auth-userfiles uses Maven for managing builds. After installing Maven you can build a
suitable jar for deployment with `mvn package`.

The resulting jar file will be placed in `target/guacamole-auth-userfiles-<version>.jar`.

## Deployment & Configuration

Copy `guacamole-auth-userfiles-<version>.jar` to the location specified by
[`lib-directory`][config-classpath] in `guacamole.properties`. Then set the
`auth-provider` property to `net.sourceforge.guacamole.net.auth.userfiles.UserFilesAuthenticationProvider`.

[config-classpath]: http://guac-dev.org/doc/gug/configuring-guacamole.html#idp380240

## Usage

You have the to store several files into "GUACAMOLE_HOME"
This path can be set in `/etc/default/tomcat7`
`GUACAMOLE_HOME=/etc/guacamole`

By default it will be:
`/usr/share/tomcat7/.guacamole/`

So place at first the 2 empty  config files for not authenticated users.

`user-mapping.xml`

  <user-mapping>

      <!-- This needs to be empty. -->

  </user-mapping>

`noauth-config.xml`

  <configs>

  </configs>

Now you has for each user a seperated config file.

If the url is: `http://localhost:8080/guacamole/#/?username=mst_henh&ident=1337`
The default configuration: `mst_henh_1337_noauth-config.xml` will be loadet.

If the url is: `http://localhost:8080/guacamole/#/?ident=1337`
The default configuration: `anonymous_337_noauth-config.xml` will be loadet.

The format of `*noauth-config.xml` will be the same as of the noauth plugin.

Example:

  <configs>
          <config name="RDP" protocol="rdp">
                  <param name="hostname" value="192.168.110.130" />      <!-- FQDN oder IP des Zielhost -->
                  <param name="port" value="3389" />                   <!-- Port, Standard ist 3389 -->
                  <param name="username" value="your rdp user" />       <!-- Anmeldename / Benutzername -->
                  <param name="password" value="****" />        <!-- Password für den Benutzer -->
                  <param name="domain" value="WORKGROUP" />              <!-- Domäne des Benutzer, ggf. Hostname des Ziels -->
                  <param name="security" value="nla" />    
                  <param name="disable-audio" value="true" />          <!-- Audio-Übertragung deaktivieren -->
                  <param name="console" value="false" />                <!-- sorgt z.B. bei Terminalserver dafür die Consolen-Sitzung zu bekommen, ansonsten sinnlos -->
                  <param name="server-layout" value="de-de-qwertz" />  <!-- mit deutscher Tastatur verbinden -->
                  <param name="ignore-cert" value="true" />            <!-- alle Zertifikate akzeptieren -->
                  <param name="enable-drive" value="true" />
                  <param name="drive-path" value="/home/guacdshare/" />
          </config>

          <config name="RDP - pchart" protocol="rdp">
                  <param name="hostname" value="192.168.110.130" />      <!-- FQDN oder IP des Zielhost -->
                  <param name="port" value="3389" />                   <!-- Port, Standard ist 3389 -->
                  <param name="username" value="your rdp user" />       <!-- Anmeldename / Benutzername -->
                  <param name="password" value="****" />        <!-- Password für den Benutzer -->
                  <param name="domain" value="WORKGROUP" />              <!-- Domäne des Benutzer, ggf. Hostname des Ziels -->
                  <param name="security" value="nla" />   
                  <param name="disable-audio" value="true" />          <!-- Audio-Übertragung deaktivieren -->
                  <param name="console" value="false" />                <!-- sorgt z.B. bei Terminalserver dafür die Consolen-Sitzung zu bekommen, ansonsten sinnlos -->
                  <param name="server-layout" value="de-de-qwertz" />  <!-- mit deutscher Tastatur verbinden -->
                  <param name="ignore-cert" value="true" />            <!-- alle Zertifikate akzeptieren -->
                  <param name="enable-drive" value="true" />
                  <param name="drive-path" value="/home/guacdshare/" />
                  <param name="remote-app" value="||pChart" />
                  <param naem="remote-app-dir" value="C:\PromosNT\bin\" />
          </config>

          <config name="SSH" protocol="ssh">
                  <param name="hostname" value="172.18.8.20" />      <!-- FQDN oder IP des Zielhost -->
                  <param name="port" value="22" />                     <!-- Port, Standard ist 22 -->
                  <param name="username" value="your linux user" />             <!-- Anmeldename / Benutzername -->
                  <param name="password" value="****" />        <!-- Password für den Benutzer -->
                  <param name="enable-sftp" value="true" />
          </config>
  </configs>

## Request Signing

Requests must be signed with an HMAC, where the message content is generated
from the request parameters as follows:

 1. The parameters `timestamp`, and `guac.protocol` are concatenated.
 2. For each of `guac.username`, `guac.password`, `guac.hostname`, and `guac.port`;
    if the parameter was included in the request, append it's unprefixed name
    (e.g. - `guac.username` becomes `username`) followed by it's value.

### Compiling

  mvn package ; \
    cp /srv/guacamole-auth-userfiles/target/guacamole-auth-userfiles-0.9.8.jar /var/lib/guacamole/extensions/ ; \
    rm /var/log/tomcat6/* ; service tomcat6 restart ; \
    tail -f /var/log/tomcat6/catalina.out

## License

MIT License

