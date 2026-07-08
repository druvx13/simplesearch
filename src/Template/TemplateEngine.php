<?php

namespace SimpleSearch\Template;

class TemplateEngine
{
    private string $templatesPath;
    private string $layoutPath;
    private array $globalData = [];

    public function __construct(string $templatesPath)
    {
        $this->templatesPath = rtrim($templatesPath, '/');
        $this->layoutPath = $this->templatesPath . '/layout';
    }

    /**
     * Set global data that will be available in ALL templates.
     * Use this for assetBase, siteName, etc.
     */
    public function setGlobalData(array $data): void
    {
        $this->globalData = $data;
    }

    public function render(string $template, array $viewData = []): string
    {
        $templateFile = $this->templatesPath . '/' . $template . '.php';

        if (!file_exists($templateFile)) {
            throw new \RuntimeException("Template not found: {$templateFile}");
        }

        // Global data is overridden by view-specific data
        $data = array_merge($this->globalData, $viewData);
        extract($data, EXTR_SKIP);

        ob_start();
        include $templateFile;
        $content = ob_get_clean();

        return $content;
    }

    public function renderWithLayout(string $template, array $viewData = [], ?string $layout = null): string
    {
        // Render the main content first
        $viewData['content'] = $this->render($template, $viewData);

        // Render with layout wrapper
        $layoutFile = $this->layoutPath . '/' . ($layout ?? 'main') . '.php';

        if (!file_exists($layoutFile)) {
            return $viewData['content'];
        }

        // Merge global data with view data for layout
        $data = array_merge($this->globalData, $viewData);
        extract($data, EXTR_SKIP);

        ob_start();
        include $layoutFile;
        return ob_get_clean();
    }

    public function partial(string $template, array $viewData = []): string
    {
        return $this->render($template, $viewData);
    }

    public function getTemplatesPath(): string
    {
        return $this->templatesPath;
    }
}
