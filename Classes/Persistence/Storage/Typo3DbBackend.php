<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Oliver Hader <oliver.hader@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Tweaked TYPO3 Database Backend implementation.
 *
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
class Tx_ExtbaseTweaks_Persistence_Storage_Typo3DbBackend extends Tx_Extbase_Persistence_Storage_Typo3DbBackend {

	/**
	 * @var array
	 */
	protected $queryStack = array();

	/**
	 * Returns the object data matching the $query.
	 *
	 * @param Tx_Extbase_Persistence_QueryInterface $query
	 * @return array
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getObjectDataByQuery(Tx_Extbase_Persistence_QueryInterface $query) {
		array_push($this->queryStack, $query);
		$rows = parent::getObjectDataByQuery($query);
		array_pop($this->queryStack);

		return $rows;
	}

	/**
	 * Returns the number of tuples matching the query.
	 *
	 * @param Tx_Extbase_Persistence_QueryInterface $query
	 * @return integer The number of matching tuples
	 */
	public function getObjectCountByQuery(Tx_Extbase_Persistence_QueryInterface $query) {
		array_push($this->queryStack, $query);
		$count = parent::getObjectCountByQuery($query);
		array_pop($this->queryStack);

		return $count;
	}


	/**
	 * @return NULL|Tx_Extbase_Persistence_QueryInterface
	 */
	protected function getCurrentQuery() {
		$currentQuery = end($this->queryStack);

		if (!$currentQuery instanceof Tx_Extbase_Persistence_QueryInterface) {
			$currentQuery = NULL;
		}

		return $currentQuery;
	}

	/**
	 * Builds the language field statement
	 *
	 * @param string $tableName The database table name
	 * @param array &$sql The query parts
	 * @return void
	 */
	protected function addSysLanguageStatement($tableName, array &$sql) {
		if ($this->addCurrentLanguageStatement($tableName, $sql) === FALSE) {
			parent::addSysLanguageStatement($tableName, $sql);
		}
	}

	/**
	 * Builds the language field statement for current language.
	 *
	 * @param string $tableName The database table name
	 * @param array &$sql The query parts
	 * @return boolean
	 */
	protected function addCurrentLanguageStatement($tableName, array &$sql) {
		$successful = FALSE;

		$className = $this->getCurrentQuery()->getType();

		if (!empty($className)) {
			$settings = $this->getPersistenceClassNameSettings($className);

			$currentLanguage = (int) $this->getFrontend()->sys_language_uid;
			$languageField = $this->getLanguageField($tableName);

			if ($languageField !== NULL && $currentLanguage > 0 && !empty($settings['query']['useCurrentLanguage'])) {
				$sql['additionalWhereClause'][] = $tableName . '.' . $languageField . '=' . $currentLanguage;
				$successful = TRUE;
			}
		}

		return $successful;
	}

	/**
	 * @param string $className
	 * @return NULL|array
	 */
	protected function getPersistenceClassNameSettings($className) {
		$settings = NULL;

		$frameworkConfiguration = $this->configurationManager->getConfiguration(
			Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
		);

		if (!empty($frameworkConfiguration['persistence']['classes'][$className])) {
			$settings = $frameworkConfiguration['persistence']['classes'][$className];
		}

		return $settings;
	}

	/**
	 * @param string $tableName
	 * @return NULL|string
	 */
	protected function getLanguageField($tableName) {
		$languageField = NULL;

		if (!empty($GLOBALS['TCA'][$tableName]['ctrl']['languageField'])) {
			$languageField = $GLOBALS['TCA'][$tableName]['ctrl']['languageField'];
		}

		return $languageField;
	}

	/**
	 * @return boolean
	 */
	protected function isFrontendMode() {
		return (defined('TYPO3_MODE') && TYPO3_MODE === 'FE' && $this->getFrontend() !== NULL);
	}

	/**
	 * @return NULL|tslib_fe
	 */
	protected function getFrontend() {
		$frontend = NULL;

		if (!empty($GLOBALS['TSFE'])) {
			$frontend = $GLOBALS['TSFE'];
		}

		return $frontend;
	}

}

?>