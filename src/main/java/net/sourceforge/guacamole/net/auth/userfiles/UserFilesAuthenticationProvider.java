package net.sourceforge.guacamole.net.auth.userfiles;

import java.util.Map;
import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.IOException;
import java.io.Reader;
import java.util.regex.Pattern;
import org.glyptodon.guacamole.GuacamoleException;
import org.glyptodon.guacamole.GuacamoleServerException;
import org.glyptodon.guacamole.environment.Environment;
import org.glyptodon.guacamole.environment.LocalEnvironment;
import org.glyptodon.guacamole.net.auth.simple.SimpleAuthenticationProvider;
import org.glyptodon.guacamole.net.auth.Credentials;
import org.glyptodon.guacamole.protocol.GuacamoleConfiguration;
import org.slf4j.LoggerFactory;
import org.slf4j.Logger;
import org.xml.sax.InputSource;
import org.xml.sax.SAXException;
import org.xml.sax.XMLReader;
import org.xml.sax.helpers.XMLReaderFactory;
import javax.servlet.http.HttpServletRequest;

/**
 * Disable authentication in Guacamole. All users accessing Guacamole are
 * automatically authenticated as "Anonymous" user and are able to use all
 * available GuacamoleConfigurations.
 *
 * Example `noauth-config.xml`:
 *
 *  <configs>
 *    <config name="my-rdp-server" protocol="rdp">
 *      <param name="hostname" value="my-rdp-server-hostname" />
 *      <param name="port" value="3389" />
 *    </config>
 *  </configs>
 *
 * The default configuration: /etc/guacamole/noauth-config.xml should only contain 
 *  <configs>
 *  </configs>
 * 
 * If the url is:
 *   http://localhost:8080/guacamole/#/?username=mst_henh&ident=1337
 *   The default configuration: /etc/guacamole/mst_henh_1337_noauth-config.xml will be loadet.
 * 
 * If the url is:
 *   http://localhost:8080/guacamole/#/?ident=1337
 *   The default configuration: /etc/guacamole/anonymous_337_noauth-config.xml will be loadet.
 * 
 * @author Heiko Henning
 */
public class UserFilesAuthenticationProvider extends SimpleAuthenticationProvider {

    /**
     * Logger for this class.
     */
    private Logger logger = LoggerFactory.getLogger(UserFilesAuthenticationProvider.class);

    /**
     * Map of all known configurations, indexed by identifier.
     */
    private Map<String, GuacamoleConfiguration> configs;

    /**
     * The last time the configuration XML was modified, as milliseconds since
     * UNIX epoch.
     */
    private long configTime;

    /**
     * Guacamole server environment.
     */
    private final Environment environment;
    
    /**
     * The default filename to use for the configuration, if not defined within
     * guacamole.properties.
     */
    public static final String DEFAULT_NOAUTH_CONFIG = "noauth-config.xml";

    /**
     * Creates a new UserFilesAuthenticationProvider that does not perform any
     * authentication at all. All attempts to access the Guacamole system are
     * presumed to be authorized.
     *
     * @throws GuacamoleException
     *     If a required property is missing, or an error occurs while parsing
     *     a property.
     */
    public UserFilesAuthenticationProvider() throws GuacamoleException {
        environment = new LocalEnvironment();
    }

    @Override
    public String getIdentifier() {
        return "userfilesauth";
    }

    /**
     * Retrieves the user configuration file, as defined within guacamole.properties.
     *
     * @return The configuration file, as defined within guacamole.properties.
     * @throws GuacamoleException If an error occurs while reading the
     *                            property.
     */
    private File getConfigurationFile(String prefix) throws GuacamoleException {

        // Get config file, defaulting to GUACAMOLE_HOME/noauth-config.xml
        File configFile;
        
        if (!prefix.isEmpty()) {
            
            if (!prefix.matches("^[\\w \\-öÖäÄüÜßèéêù]+$")) {
                throw new GuacamoleServerException("Invalid username or ident.");
            }
            
            configFile = new File(environment.getGuacamoleHome(), prefix + DEFAULT_NOAUTH_CONFIG);
        } else {
            configFile = new File(environment.getGuacamoleHome(), DEFAULT_NOAUTH_CONFIG);
        }

        return configFile;

    }
    
    public synchronized void init() throws GuacamoleException {
        init("");
    }

    public synchronized void init(String prefix) throws GuacamoleException {

        // Get configuration file
        File configFile = getConfigurationFile(prefix);
        logger.debug("Reading configuration file: \"{}\"", configFile);

        // Parse document
        try {

            // Set up parser
            UserFilesAuthConfigContentHandler contentHandler = new UserFilesAuthConfigContentHandler();

            XMLReader parser = XMLReaderFactory.createXMLReader();
            parser.setContentHandler(contentHandler);

            // Read and parse file
            Reader reader = new BufferedReader(new FileReader(configFile));
            parser.parse(new InputSource(reader));
            reader.close();

            // Init configs
            configTime = configFile.lastModified();
            configs = contentHandler.getConfigs();

        }
        catch (IOException e) {
            throw new GuacamoleServerException("Error reading configuration file.", e);
        }
        catch (SAXException e) {
            throw new GuacamoleServerException("Error parsing XML file.", e);
        }

    }

    @Override
    public Map<String, GuacamoleConfiguration> getAuthorizedConfigurations(Credentials credentials) throws GuacamoleException {
        HttpServletRequest request = credentials.getRequest();
        
        String username = request.getParameter("username");
        String ident = request.getParameter("ident");
        
        // Set username if given.
        if (!username.isEmpty()) {
            logger.debug("Set username: {}", username);
            credentials.setUsername(username);
        }
        
        /** Debug all get variables.
        @SuppressWarnings("unchecked")
        Map<String, String[]> params = request.getParameterMap();

        for (String name : params.keySet()) {
            String value = request.getParameter(name);

			logger.debug("kv: {} = {}", name, value);
        }
        **/
        
        String prefix = null;
        if (!username.isEmpty() && !ident.isEmpty()) {
            prefix = username + "_" + ident + "_";
        } else if (!ident.isEmpty()) {
            prefix = "anonymous_" + ident + "_";
        }
        
        // Check mapping file mod time
        File configFile = getConfigurationFile(prefix);
        
        if (configFile.exists() && configTime < configFile.lastModified()) {

            // If modified recently, gain exclusive access and recheck
            synchronized (this) {
                if (configFile.exists() && configTime < configFile.lastModified()) {
                    logger.debug("Configuration file \"{}\" has been modified.", configFile);
                    init(prefix); // If still not up to date, re-init
                }
            }

        }

        // If no mapping available, report as such
        if (configs == null) {
            throw new GuacamoleServerException("Configuration could not be read.");
        }

        return configs;

    }
}
