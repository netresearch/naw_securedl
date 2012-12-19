#
# Table structure for table 'tx_nawsecuredl_counter'
#
CREATE TABLE tx_nawsecuredl_counter (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    filename text NOT NULL,
    userid int(11) DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);