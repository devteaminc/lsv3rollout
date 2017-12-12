<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
namespace Amasty\Label\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;

class Shape extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->messageManager = $messageManager;
        $this->filesystem = $filesystem;
        $this->urlBuilder = $context->getUrlBuilder();
    }

    protected $_shapeTypes = [
        'circle'        => 'Circle',
        'rquarter'      => 'Right Quarter',
        'rbquarter'      => 'Right Bottom Quarter',
        'lquarter'      => 'Left Quarter',
        'lbquarter'      => 'Left Bottom Quarter',
        'list'          => 'List',
        'note'          => 'Note',
        'flag'          => 'Flag',
        'banner'        => 'Banner',
        'tag'           => 'Tag',
    ];

    public function getShapes()
    {
        return $this->_shapeTypes;
    }

    public function generateNewLabel($shape, $color)
    {
        $color = str_replace('#', '', $color);
        $fileName =  $shape . '_' . $color . '.svg';
        $svg =   $this->_getLabelFolder() . $fileName;
        if (file_exists($svg)) {
            return $fileName;
        } else {
            $svg =   $this->_getLabelFolder() . $shape . '.svg';
            if (file_exists($svg)) {
                $fileContents = $this->_changeColorImage($svg, $color);
                if ($fileContents) {
                    $newName =  $this->_getLabelFolder() . $fileName;
                    if ($this->_copyAndRenameImage($fileContents, $newName)) {
                        return $fileName;
                    }
                }
            }
        }

        return false;
    }

    public function generateShape($shape, $type, $checked)
    {
        $html = '<div class="amlabel-shape">';
        $html .= '<input ' . $checked . ' type="radio" value="' . $shape . '" name="shape_type' .
            $type . '" id="shape_' . $shape . $type . '">';
        $svg =   $this->_getLabelFolder() . $shape . '.svg';
        if (file_exists($svg)) {
            $svg = $this->_getLabelPath()  . $shape . '.svg';
            $html .=   '<label for="shape_' . $shape . $type . '">';
            $html .= '<img src="' . $svg . '" class="amlabel-shape-image">';
            $html .= '</label>';
        }

        $html .= '</div>';
        return $html;
    }

    protected function _changeColorImage($imageSvgFile, $color)
    {
        $fileContents = file_get_contents($imageSvgFile);
        $document = new \DOMDocument();
        $document->preserveWhiteSpace = false;
        if ($document->loadXML($fileContents)) {
            $allTags = $document->getElementsByTagName("path");
            foreach ($allTags as $tag) {
                $vectorColor = $tag->getAttribute('fill');
                if (strtoupper($vectorColor) != '#FFFFFF') {
                    $tag->setAttribute('fill', '#' . $color);
                    $fileContents = $document->saveXML($document);
                    return $fileContents;
                }
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('Failed to load SVG file %1 as XML.  It probably contains malformed data.', $imageSvgFile)
            );
            return false;
        }

        return $fileContents;
    }

    protected function _copyAndRenameImage($fileContents, $newName)
    {
        try {
            file_put_contents($newName, $fileContents);
            return true;
        } catch (\Exception $exc) {
            $this->messageManager->addErrorMessage($exc->getMessage());
            return false;
        }
    }

    protected function _getLabelFolder()
    {
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            'amasty/amlabel/'
        );

        return $path;
    }

    protected function _getLabelPath()
    {
        $path = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);
        $path .= 'amasty/amlabel/';
        return $path;
    }
}
