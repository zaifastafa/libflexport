<?php

namespace FINDOLOGIC\Export;

use FINDOLOGIC\Export\CSV\CSVExporter;
use FINDOLOGIC\Export\Data\Item;
use FINDOLOGIC\Export\XML\XMLExporter;
use InvalidArgumentException;

abstract class Exporter
{
    /**
     * XML-based export format.
     *
     * @see https://docs.findologic.com/doku.php?id=export_patterns:xml
     */
    public const TYPE_XML = 0;

    /**
     * CSV-based export format. Does not support usergroups.
     *
     * @see https://docs.findologic.com/doku.php?id=export_patterns:csv
     */
    public const TYPE_CSV = 1;

    protected $itemsPerPage;

    protected function __construct($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * Creates an exporter for the desired output format.
     *
     * @param int $type The type of export format to choose. Must be either Exporter::TYPE_XML or Exporter::TYPE_CSV.
     * @param int $itemsPerPage Number of items being exported at once. Respecting this parameter is at the exporter
     *      implementation's discretion.
     * @param array $csvProperties Properties/extra columns for CSV export. Has no effect for XML export.
     * @return Exporter The exporter for the desired output format.
     */
    public static function create(int $type, int $itemsPerPage = 20, array $csvProperties = []): Exporter
    {
        if ($itemsPerPage < 1) {
            throw new InvalidArgumentException('At least one item must be exported per page.');
        }

        switch ($type) {
            case self::TYPE_XML:
                $exporter = new XMLExporter($itemsPerPage);
                break;
            case self::TYPE_CSV:
                $exporter = new CSVExporter($itemsPerPage, $csvProperties);
                break;
            default:
                throw new InvalidArgumentException('Unsupported exporter type.');
        }

        return $exporter;
    }

    /**
     * Turns the provided items into their serialized form.
     *
     * @param Item[] $items Array of items to serialize. All of them are serialized, regardless of $start and $total.
     * @param int $start Assuming that $items is a fragment of the total, this is the global index of the first item in
     *      $items.
     * @param int $count The number of items requested for this export step. Actual number of items can be smaller due
     *      to errors, and can not be greater than the requested count, because that would indicate that the requested
     *      count is ignored when generating items. This value is ignored when using CSV exporter.
     * @param int $total The global total of items that could be exported. This value is ignored when using CSV
     *      exporter.
     * @return string The items in serialized form.
     */
    abstract public function serializeItems(array $items, int $start, int $count, int $total): string;

    /**
     * Like serializeItems(), but the output is written to filesystem instead of being returned.
     *
     * @param string $targetDirectory The directory to which the file is written. The filename is at the exporter
     *      implementation's discretion.
     * @param Item[] $items Array of items to serialize. All of them are serialized, regardless of $start and $total.
     * @param int $start Assuming that $items is a fragment of the total, this is the global index of the first item in
     *      $items.
     * @param int $count The number of items requested for this export step. Actual number of items can be smaller due
     *      to errors, and can not be greater than the requested count, because that would indicate that the requested
     *      count is ignored when generating items. This value is ignored when using CSV exporter.
     * @param int $total The global total of items that could be exported. This value is ignored when using CSV
     *      exporter.
     * @return string Full path of the written file.
     */
    abstract public function serializeItemsToFile(
        string $targetDirectory,
        array $items,
        int $start,
        int $count,
        int $total
    ): string;

    /**
     * Creates an export format-specific item instance.
     *
     * @param string $id Unique ID of the item.
     * @return Item The newly generated item.
     */
    abstract public function createItem($id): Item;
}
