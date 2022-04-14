package net.sourceforge.guacamole.net.auth.userfiles;

import java.util.Map;
import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.IOException;
import java.io.Reader;
import java.util.Date;
import java.util.UUID;
import org.apache.guacamole.GuacamoleException;
import org.apache.guacamole.GuacamoleServerException;
import org.apache.guacamole.environment.Environment;
import org.apache.guacamole.environment.LocalEnvironment;
import org.apache.guacamole.net.auth.Credentials;
import org.apache.guacamole.protocol.GuacamoleConfiguration;
import org.slf4j.LoggerFactory;
import org.slf4j.Logger;
import org.xml.sax.InputSource;
import org.xml.sax.SAXException;
import org.xml.sax.XMLReader;
import org.xml.sax.helpers.XMLReaderFactory;
import javax.servlet.http.HttpServletRequest;
import org.apache.guacamole.net.auth.AbstractAuthenticatedUser;
import org.apache.guacamole.net.auth.AuthenticatedUser;
import org.apache.guacamole.net.auth.AuthenticationProvider;
import org.apache.guacamole.net.auth.UserContext;
import org.apache.guacamole.net.auth.simple.SimpleUserContext;
import org.apache.guacamole.token.StandardTokens;
import org.apache.guacamole.token.TokenFilter;

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
public class UserFilesAuthenticationProvider implements AuthenticationProvider {

    /**
     * Logger for this class.
     */
    private final Logger logger = LoggerFactory.getLogger(UserFilesAuthenticationProvider.class);

    /**
     * The last time the configuration XML was modified, as milliseconds since
     * UNIX epoch.
     */
    //private long config_time;

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
     * AuthenticatedUser which contains its own predefined set of authorized
     * configurations.
     *
     * @author Michael Jumper
     */
    private class UserFilesAuthenticatedUser extends AbstractAuthenticatedUser {

        /**
         * The credentials provided when this AuthenticatedUser was
         * authenticated.
         */
        private final Credentials credentials;

        /**
         * The GuacamoleConfigurations that this AuthenticatedUser is
         * authorized to use.
         */
        private Map<String, GuacamoleConfiguration> configs;

        /**
         * Creates a new SimpleAuthenticatedUser associated with the given
         * credentials and having access to the given Map of
         * GuacamoleConfigurations.
         *
         * @param credentials
         *     The credentials provided by the user when they authenticated.
         *
         * @param configs
         *     A Map of all GuacamoleConfigurations for which this user has
         *     access. The keys of this Map are Strings which uniquely identify
         *     each configuration.
         */
        public UserFilesAuthenticatedUser(Credentials credentials, Map<String, GuacamoleConfiguration> configs) {

            // Store credentials and configurations
            this.credentials = credentials;
            this.configs = configs;

            // Pull username from credentials if it exists
            String username = credentials.getUsername();
            if (username != null && !username.isEmpty()) {
                setIdentifier(username);

            // Otherwise generate a random username
            } else {
                setIdentifier(UUID.randomUUID().toString());
            }

        }

        /**
         * Returns a Map containing all GuacamoleConfigurations that this user
         * is authorized to use. The keys of this Map are Strings which
         * uniquely identify each configuration.
         *
         * @return
         *     A Map of all configurations for which this user is authorized.
         */
        public Map<String, GuacamoleConfiguration> getAuthorizedConfigurations() {
            return configs;
        }

        public void setAuthorizedConfigurations(Map<String, GuacamoleConfiguration> configs) {
            this.configs = configs;
        }

        @Override
        public AuthenticationProvider getAuthenticationProvider() {
            return UserFilesAuthenticationProvider.this;
        }

        @Override
        public Credentials getCredentials() {
            return credentials;
        }
    }

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

        if (prefix != null && !prefix.isEmpty()) {
            // Check file path dont breaks folder.
            if (!prefix.matches("^[\\w \\-\\.öÖäÄüÜßèéêù]+$")) {
                throw new GuacamoleServerException("Invalid username or ident.");
            }

            configFile = new File(environment.getGuacamoleHome(), prefix + DEFAULT_NOAUTH_CONFIG);
        } else {
            configFile = new File(environment.getGuacamoleHome(), DEFAULT_NOAUTH_CONFIG);
        }

        return configFile;
    }

    /**
     * Parse guacamole configuration xml.
     *
     * @param username
     * @param ident
     * @return
     * @throws GuacamoleException
     */
    public Map<String, GuacamoleConfiguration> parseConfigFile(String username, String ident) throws GuacamoleException {
        String prefix = null;
        if (!username.isEmpty() && !ident.isEmpty()) {
            prefix = username + "_" + ident + "_";
        } else if (!ident.isEmpty()) {
            prefix = "anonymous_" + ident + "_";
        }

        // Check mapping file mod time
        File configFile = getConfigurationFile(prefix);
        Map<String, GuacamoleConfiguration> configs = null;

        if (configFile.exists()) {

            // If modified recently, gain exclusive access and recheck
            synchronized (this) {
                if (configFile.exists()) {
                    logger.debug("Parse configuration file \"{}\".", configFile);
                    configs = parseConfigFile(prefix); // If still not up to date, re-init
                }
            }
        }

        return configs;
    }

    public synchronized Map<String, GuacamoleConfiguration> parseConfigFile() throws GuacamoleException {
        return parseConfigFile("");
    }

    public synchronized Map<String, GuacamoleConfiguration> parseConfigFile(String prefix) throws GuacamoleException {

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

            Map<String, GuacamoleConfiguration> configs = null;

            // Check if config is valid and use/init.
            Date now = new Date();
            if (contentHandler.getValidTo() == null || contentHandler.getValidTo().compareTo(now) >= 0) {
                // Parse configs xml file to Object.
                //config_time = configFile.lastModified();
                configs = contentHandler.getConfigs();
            } else {
                logger.warn("Ignore config: \"{}\" because its valid_to \"{}\" is outdated.", configFile, contentHandler.getValidTo().toString());
            }

            logger.debug("getDeleteConfig: {}", ((contentHandler.getDeleteConfig() == true) ? "Yes" : "No"));
            if (contentHandler.getDeleteConfig() == true) {
                try {
                    configFile.delete();
                } catch (Exception e) {
                    logger.warn("Error deleting config file: \"{}\": \"{}\"", configFile, e.getMessage());
                }
            }

            return configs;

        }
        catch (IOException e) {
            throw new GuacamoleServerException("Error reading configuration file.", e);
        }
        catch (SAXException e) {
            throw new GuacamoleServerException("Error parsing XML file.", e);
        }

    }

    public Map<String, GuacamoleConfiguration> getAuthorizedConfigurations(Credentials credentials) throws GuacamoleException {
        HttpServletRequest request = credentials.getRequest();

        String username = request.getParameter("username");
        String ident = request.getParameter("ident");

        // Avoid null point exceptions.
        if (username == null) {
            username = "";
        }

        // Avoid null point exceptions.
        if (ident == null) {
            ident = "";
        }

        /** Debug all HTTP_GET variables.
        @SuppressWarnings("unchecked")
        Map<String, String[]> params = request.getParameterMap();

        for (String name : params.keySet()) {
            String value = request.getParameter(name);

			logger.debug("kv: {} = {}", name, value);
        }
        **/

        Map<String, GuacamoleConfiguration> configs = parseConfigFile(username, ident);

        // If no mapping available, report as such
        if (configs == null) {
            throw new GuacamoleServerException("Configuration could not be read.");
        } else {
            // Set username if given.
            if (!username.isEmpty()) {
                logger.debug("Set username: {}", username);
                credentials.setUsername(username);
            }
        }

        return configs;
    }

    /**
     * Given an arbitrary credentials object, returns a Map containing all
     * configurations authorized by those credentials, filtering those
     * configurations using a TokenFilter and the standard credential tokens
     * (like ${GUAC_USERNAME} and ${GUAC_PASSWORD}). The keys of this Map
     * are Strings which uniquely identify each configuration.
     *
     * @param credentials
     *     The credentials to use to retrieve authorized configurations.
     *
     * @return
     *     A Map of all configurations authorized by the given credentials, or
     *     null if the credentials given are not authorized.
     *
     * @throws GuacamoleException
     *     If an error occurs while retrieving configurations.
     */
    private Map<String, GuacamoleConfiguration> getFilteredAuthorizedConfigurations(Credentials credentials)
            throws GuacamoleException {

        // Get configurations
        Map<String, GuacamoleConfiguration> configs = getAuthorizedConfigurations(credentials);

        // Return as unauthorized if not authorized to retrieve configs
        if (configs == null) {
            return null;
        }

        // Build credential TokenFilter
        TokenFilter tokenFilter = new TokenFilter();
        StandardTokens.addStandardTokens(tokenFilter, credentials);

        // Filter each configuration
        for (GuacamoleConfiguration config : configs.values()) {
            tokenFilter.filterValues(config.getParameters());
        }

        return configs;
    }

    /**
     * Given a user who has already been authenticated, returns a Map
     * containing all configurations for which that user is authorized,
     * filtering those configurations using a TokenFilter and the standard
     * credential tokens (like ${GUAC_USERNAME} and ${GUAC_PASSWORD}). The keys
     * of this Map are Strings which uniquely identify each configuration.
     *
     * @param authenticatedUser
     *     The user whose authorized configurations are to be retrieved.
     *
     * @return
     *     A Map of all configurations authorized for use by the given user, or
     *     null if the user is not authorized to use any configurations.
     *
     * @throws GuacamoleException
     *     If an error occurs while retrieving configurations.
     */
    private Map<String, GuacamoleConfiguration> getFilteredAuthorizedConfigurations(AuthenticatedUser authenticatedUser)
            throws GuacamoleException {

        // Pull cached configurations, if any
        if (authenticatedUser instanceof UserFilesAuthenticationProvider.UserFilesAuthenticatedUser && authenticatedUser.getAuthenticationProvider() == this) {
            return ((UserFilesAuthenticationProvider.UserFilesAuthenticatedUser) authenticatedUser).getAuthorizedConfigurations();
        }

        // Otherwise, pull using credentials
        return getFilteredAuthorizedConfigurations(authenticatedUser.getCredentials());

    }

    @Override
    public AuthenticatedUser authenticateUser(final Credentials credentials)
            throws GuacamoleException {

        // Get configurations
        Map<String, GuacamoleConfiguration> configs = getFilteredAuthorizedConfigurations(credentials);

        // Return as unauthorized if not authorized to retrieve configs
        if (configs == null) {
            return null;
        }

        return new UserFilesAuthenticationProvider.UserFilesAuthenticatedUser(credentials, configs);

    }

    @Override
    public UserContext getUserContext(AuthenticatedUser authenticatedUser)
            throws GuacamoleException {

        // Get configurations
        Map<String, GuacamoleConfiguration> configs = getFilteredAuthorizedConfigurations(authenticatedUser);

        // Return as unauthorized if not authorized to retrieve configs
        if (configs == null) {
            return null;
        }

        // Return user context restricted to authorized configs
        return new SimpleUserContext(this, authenticatedUser.getIdentifier(), configs);

    }

    @Override
    // Re parse config xml for loged in user on each page refresh.
    public AuthenticatedUser updateAuthenticatedUser(AuthenticatedUser authenticatedUser, Credentials credentials)
            throws GuacamoleException {

        // Get configurations
        Map<String, GuacamoleConfiguration> configs = getFilteredAuthorizedConfigurations(credentials);

        // Return as unauthorized if not authorized to retrieve configs
        if (configs == null) {
            return null;
        }

        return new UserFilesAuthenticationProvider.UserFilesAuthenticatedUser(credentials, configs);
    }

    @Override
     // Create new user context wih new config.
    public UserContext updateUserContext(UserContext context, AuthenticatedUser authenticatedUser, Credentials credentials)
            throws GuacamoleException {

        // Get configurations
        Map<String, GuacamoleConfiguration> configs = getFilteredAuthorizedConfigurations(credentials);

        // Return as unauthorized if not authorized to retrieve configs
        if (configs == null) {
            return null;
        }

        // Return user context restricted to authorized configs
        return new SimpleUserContext(this, authenticatedUser.getIdentifier(), configs);
    }

    @Override
    public UserContext decorate(UserContext context,
            AuthenticatedUser authenticatedUser,
            Credentials credentials) throws GuacamoleException {

		return context;
	}

    @Override
    public UserContext redecorate(UserContext decorated, UserContext context,
            AuthenticatedUser authenticatedUser,
            Credentials credentials) throws GuacamoleException {

	   return context;
	}

    @Override
    public void shutdown() {
    	;
    }

    @Override
    public Object getResource() throws GuacamoleException {
    	return null;
    }
}
