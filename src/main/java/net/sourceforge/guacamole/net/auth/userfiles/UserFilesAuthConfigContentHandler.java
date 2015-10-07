package net.sourceforge.guacamole.net.auth.userfiles;

import java.text.DateFormat;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Collections;
import java.util.Date;
import java.util.HashMap;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.glyptodon.guacamole.protocol.GuacamoleConfiguration;
import org.slf4j.LoggerFactory;
import org.xml.sax.Attributes;
import org.xml.sax.SAXException;
import org.xml.sax.helpers.DefaultHandler;

/**
 * XML parser for the configuration file used by the UserFilesAuth auth provider.
 *
 * @author Heiko Henning
 */
public class UserFilesAuthConfigContentHandler extends DefaultHandler {

    /**
     * Logger for this class.
     */
    private org.slf4j.Logger logger = LoggerFactory.getLogger(UserFilesAuthenticationProvider.class);
    
    /**
     * Map of all configurations, indexed by name.
     */
    private Map<String, GuacamoleConfiguration> configs = new HashMap<String, GuacamoleConfiguration>();

    /**
     * The name of the current configuration, if any.
     */
    private String current = null;

    /**
     * The current configuration being parsed, if any.
     */
    private GuacamoleConfiguration currentConfig = null;
    
    /**
     * Check if config file should be deleted.
     */
    private Boolean deleteConfig = false;
    
    /**
     * The maximum validity of this config.
     */
    private Date validTo = null;
     
    /**
     * Returns the a map of all available configurations as parsed from the
     * XML file. This map is unmodifiable.
     *
     * @return A map of all available configurations.
     */
    public Map<String, GuacamoleConfiguration> getConfigs() {
        return Collections.unmodifiableMap(configs);
    }
    
    /**
     * Return bit if config file should be deleted.
     * @return 
     */
    public Boolean getDeleteConfig() {
        return deleteConfig;
    }
    
    /**
     * Return valid to date or NULL.
     * @return 
     */
    public Date getValidTo() {
        return validTo;
    }

    @Override
    public void endElement(String uri, String localName, String qName) throws SAXException {

        // If end of config element, add to map
        if (localName.equals("config")) {

            // Add to map
            configs.put(current, currentConfig);

            // Reset state for next configuration
            currentConfig = null;
            current = null;

        }

    }

    @Override
    public void startElement(String uri, String localName, String qName, Attributes attributes) throws SAXException {
        // Begin configuration parsing if config element
        if (localName.equals("config")) {

            // Ensure this config is on the top level
            if (current != null)
                throw new SAXException("Configurations cannot be nested.");

            // Read name
            String name = attributes.getValue("name");
            if (name == null)
                throw new SAXException("Each configuration must have a name.");

            // Read protocol
            String protocol = attributes.getValue("protocol");
            if (protocol == null)
                throw new SAXException("Each configuration must have a protocol.");

            // Create config stub
            current = name;
            currentConfig = new GuacamoleConfiguration();
            currentConfig.setProtocol(protocol);

        }

        // Add parameters to existing configuration
        else if (localName.equals("param")) {

            // Ensure a corresponding config exists
            if (currentConfig == null) {
                throw new SAXException("Parameter without corresponding configuration.");
            }

            currentConfig.setParameter(attributes.getValue("name"), attributes.getValue("value"));

        } else if (localName.equals("configs")) {
            String deleteConfigStr = attributes.getValue("delete");
            
            if (deleteConfigStr != null && (
                    deleteConfigStr.toLowerCase().equals("yes") || 
                    deleteConfigStr.toLowerCase().equals("true") || 
                    deleteConfigStr.toLowerCase().equals("1"))) {
                
                deleteConfig = true;
            }
            
            String validToStr = attributes.getValue("valid_to");
            
            if (validToStr != null) {
                // Parse valid_to date for all ISO8601 kinds .
                DateFormat[] ISO8601_parsers = {
                    new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss.SSSXXX"),
                    new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss.SSSZ"),
                    new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss.SSS"),
                    new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ssXXX"),
                    new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ssZ"),
                    new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss"),
                };

                String last_error = null;
                for (DateFormat ISO8601_parser : ISO8601_parsers) {
                    try {
                        validTo = ISO8601_parser.parse(validToStr);
                        last_error = null;
                        break;
                    } catch (ParseException ex) {
                        last_error = ex.getMessage();
                    }    
                }
                
                if (last_error != null) {
                    logger.warn("Invalid \"valid_to\" = \"{}\" date. {}", validToStr, last_error);
                }
            }
            
        }

    }

}
